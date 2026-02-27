<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\Donation;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class DonationController extends Controller
{
    /**
     * Create a Stripe PaymentIntent for unlocking or tipping.
     */
    public function createIntent(Request $request, Track $track): JsonResponse
    {
        $request->validate([
            'type'    => ['required', 'in:unlock,tip'],
            'amount'  => ['required_if:type,tip', 'numeric', 'min:0.50'],
            'message' => ['nullable', 'string', 'max:280'],
        ]);

        $type = $request->input('type');
        $user = $request->user();

        // Unlock: use the track's donation_amount
        // Tip: use the user-supplied amount
        if ($type === 'unlock') {
            abort_if(!$track->requires_donation, 400, 'Track does not require a donation.');

            // Already unlocked?
            $alreadyUnlocked = Donation::where('user_id', $user->id)
                ->where('track_id', $track->id)
                ->where('type', 'unlock')
                ->where('status', 'completed')
                ->exists();

            if ($alreadyUnlocked) {
                return response()->json(['already_unlocked' => true]);
            }

            $amountCents = (int) round($track->donation_amount * 100);
        } else {
            $amountCents = (int) round($request->input('amount', 1.0) * 100);
        }

        $stripe = new StripeClient(config('cashier.secret'));

        $intent = $stripe->paymentIntents->create([
            'amount'   => $amountCents,
            'currency' => 'usd',
            'metadata' => [
                'user_id'    => $user->id,
                'track_id'   => $track->id,
                'artist_id'  => $track->artist_profile_id,
                'type'       => $type,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        // Create a pending donation record
        Donation::create([
            'user_id'                  => $user->id,
            'artist_profile_id'        => $track->artist_profile_id,
            'track_id'                 => $track->id,
            'amount'                   => $amountCents / 100,
            'stripe_payment_intent_id' => $intent->id,
            'type'                     => $type,
            'status'                   => 'pending',
            'message'                  => $request->input('message'),
        ]);

        return response()->json(['client_secret' => $intent->client_secret]);
    }

    /**
     * Handle Stripe webhook events.
     */
    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');
        $secret     = config('cashier.webhook.secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            $donation = Donation::where('stripe_payment_intent_id', $intent->id)->first();
            if ($donation) {
                $donation->update(['status' => 'completed']);

                // Bump artist total_donations
                ArtistProfile::where('id', $donation->artist_profile_id)
                    ->increment('total_donations', $donation->amount);
            }
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $intent   = $event->data->object;
            $donation = Donation::where('stripe_payment_intent_id', $intent->id)->first();
            $donation?->update(['status' => 'failed']);
        }

        return response('Webhook handled', 200);
    }
}
