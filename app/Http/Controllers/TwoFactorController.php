<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use App\Models\User;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
        $this->middleware('auth');
    }

    /**
     * Show Two-Factor Authentication setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // Generate a new secret key
        $secret = $this->google2fa->generateSecretKey();
        
        // Store the secret temporarily in session for verification
        session(['2fa_secret' => $secret]);
        
        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.2fa-setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
            'user' => $user
        ]);
    }

    /**
     * Enable Two-Factor Authentication
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:6|max:6',
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Verify user's current password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        // Get the secret from session
        $secret = session('2fa_secret');
        
        if (!$secret) {
            return back()->withErrors(['code' => 'Setup session expired. Please try again.']);
        }

        // Verify the provided code
        $valid = $this->google2fa->verifyKey($secret, $request->code);
        
        if (!$valid) {
            // Rate limiting attempts
            $this->incrementAttemptCount();
            return back()->withErrors(['code' => 'Invalid authentication code']);
        }

        // Enable 2FA for the user
        $user->update([
            'google2fa_secret' => encrypt($secret),
            'google2fa_enabled_at' => now(),
            'two_factor_confirmed_at' => now()
        ]);

        // Generate and store recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update(['recovery_codes' => $recoveryCodes]);

        // Clear the session
        session()->forget('2fa_secret');

        return redirect()->route('dashboard')
            ->with('success', 'Two-Factor Authentication has been enabled. Please save your recovery codes safely.');
    }

    /**
     * Disable Two-Factor Authentication
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string|min:6|max:6'
        ]);

        $user = Auth::user();
        
        // Verify user's current password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        // Verify the provided code if 2FA is enabled
        if ($user->google2fa_enabled_at) {
            $secret = decrypt($user->google2fa_secret);
            $valid = $this->google2fa->verifyKey($secret, $request->code);
            
            if (!$valid) {
                $this->incrementAttemptCount();
                return back()->withErrors(['code' => 'Invalid authentication code']);
            }
        }

        // Disable 2FA for the user
        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled_at' => null,
            'recovery_codes' => null,
            'two_factor_confirmed_at' => null
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Two-Factor Authentication has been disabled.');
    }

    /**
     * Show Two-Factor Authentication challenge page
     */
    public function showChallenge()
    {
        return view('auth.2fa-challenge');
    }

    /**
     * Verify Two-Factor Authentication code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:6|max:6',
            'recovery_code' => 'nullable|string|min:10|max:10'
        ]);

        $user = Auth::user();

        // Check if it's a recovery code
        if ($request->filled('recovery_code')) {
            return $this->handleRecoveryCode($request->recovery_code, $user);
        }

        // Verify TOTP code
        if (!$user->google2fa_secret) {
            return back()->withErrors(['code' => 'Two-Factor Authentication is not enabled.']);
        }

        $secret = decrypt($user->google2fa_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            $this->incrementAttemptCount();
            return back()->withErrors(['code' => 'Invalid authentication code']);
        }

        // Mark 2FA as verified for this session
        session(['2fa_verified' => true, '2fa_verified_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle recovery code verification
     */
    protected function handleRecoveryCode($recoveryCode, $user)
    {
        $recoveryCodes = $user->recovery_codes ?? [];
        
        if (!in_array($recoveryCode, $recoveryCodes)) {
            $this->incrementAttemptCount();
            return back()->withErrors(['recovery_code' => 'Invalid recovery code']);
        }

        // Remove the used recovery code
        $recoveryCodes = array_diff($recoveryCodes, [$recoveryCode]);
        $user->update(['recovery_codes' => array_values($recoveryCodes)]);

        // Mark 2FA as verified and warn user about remaining codes
        session(['2fa_verified' => true, '2fa_verified_at' => now()]);
        
        $remainingCount = count($recoveryCodes);
        $message = 'Recovery code accepted. ';
        
        if ($remainingCount <= 2) {
            $message .= "Warning: Only {$remainingCount} recovery code(s) remaining. Consider regenerating codes.";
        }

        return redirect()->intended(route('dashboard'))->with('warning', $message);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Verify user's current password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        // Generate new recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update(['recovery_codes' => $recoveryCodes]);

        return back()->with('success', 'Recovery codes have been regenerated. Please save them safely.');
    }

    /**
     * Generate secure recovery codes
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }
        return $codes;
    }

    /**
     * Increment failed attempt count with rate limiting
     */
    protected function incrementAttemptCount()
    {
        $key = '2fa_attempts_' . Auth::id();
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addMinutes(15));
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            Auth::user()->update([
                'locked_until' => now()->addMinutes(30)
            ]);
            
            Auth::logout();
            
            throw new \Illuminate\Auth\AuthenticationException(
                'Account temporarily locked due to too many failed 2FA attempts. Please try again in 30 minutes.'
            );
        }
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes()
    {
        $user = Auth::user();
        
        if (!$user->google2fa_enabled_at) {
            return redirect()->route('dashboard')
                ->with('error', 'Two-Factor Authentication is not enabled.');
        }

        $recoveryCodes = $user->recovery_codes ?? [];

        return view('auth.2fa-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
            'remainingCount' => count($recoveryCodes)
        ]);
    }
}