<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UsernameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        // Length validation
        if (strlen($value) < 3) {
            $fail('The :attribute must be at least 3 characters long.');
            return;
        }

        if (strlen($value) > 30) {
            $fail('The :attribute must not exceed 30 characters.');
            return;
        }

        // Character validation - only allow letters, numbers, underscores, and hyphens
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $fail('The :attribute may only contain letters, numbers, underscores, and hyphens.');
            return;
        }

        // Must start with a letter or number
        if (!preg_match('/^[a-zA-Z0-9]/', $value)) {
            $fail('The :attribute must start with a letter or number.');
            return;
        }

        // Cannot end with underscore or hyphen
        if (preg_match('/[_-]$/', $value)) {
            $fail('The :attribute cannot end with an underscore or hyphen.');
            return;
        }

        // No consecutive underscores or hyphens
        if (preg_match('/_{2,}|-{2,}|_-|-_/', $value)) {
            $fail('The :attribute cannot contain consecutive underscores or hyphens.');
            return;
        }

        // Reserved usernames
        if ($this->isReservedUsername($value)) {
            $fail('The :attribute is reserved and cannot be used.');
            return;
        }

        // Check for inappropriate content
        if ($this->containsInappropriateContent($value)) {
            $fail('The :attribute contains inappropriate content.');
            return;
        }

        // Check if it looks like a phone number or email
        if ($this->looksLikeContactInfo($value)) {
            $fail('The :attribute cannot resemble contact information.');
            return;
        }
    }

    private function isReservedUsername(string $username): bool
    {
        $reservedUsernames = [
            // System routes
            'admin', 'administrator', 'root', 'system', 'api', 'www', 'mail', 'ftp',
            'support', 'help', 'info', 'contact', 'about', 'terms', 'privacy', 'faq',
            'blog', 'news', 'forum', 'chat', 'shop', 'store', 'cart', 'checkout',
            
            // Music platform specific
            'music', 'track', 'tracks', 'album', 'albums', 'artist', 'artists',
            'playlist', 'playlists', 'genre', 'genres', 'song', 'songs',
            'upload', 'downloads', 'stream', 'player', 'radio', 'discover',
            
            // Common impersonation targets
            'dson', 'sadorect', 'official', 'verified', 'moderator', 'staff',
            
            // Technical terms
            'test', 'demo', 'dev', 'staging', 'production', 'null', 'undefined',
            'database', 'config', 'cache', 'session', 'cookie', 'token',
            
            // Social media platforms
            'facebook', 'twitter', 'instagram', 'youtube', 'tiktok', 'linkedin',
            'reddit', 'discord', 'telegram', 'whatsapp', 'snapchat',
            
            // General
            'user', 'guest', 'anonymous', 'public', 'private', 'secure', 'login',
            'register', 'signup', 'signin', 'logout', 'account', 'profile',
            'settings', 'dashboard', 'home', 'index', 'search', 'explore'
        ];

        return in_array(strtolower($username), $reservedUsernames);
    }

    private function containsInappropriateContent(string $username): bool
    {
        // Convert to lowercase for case-insensitive checking
        $lowerUsername = strtolower($username);
        
        // List of inappropriate words and patterns
        $inappropriatePatterns = [
            // Profanity (partial list - expand as needed)
            'fuck', 'shit', 'ass', 'bitch', 'bastard', 'damn', 'hell',
            'dick', 'pussy', 'cock', 'cunt', 'whore', 'slut',
            
            // Offensive terms
            'nazi', 'hitler', 'terrorist', 'kill', 'death', 'suicide',
            'rape', 'abuse', 'violence', 'hate', 'racist',
            
            // Sexual content
            'sex', 'porn', 'xxx', 'erotic', 'nude', 'naked',
            
            // Drug related
            'weed', 'marijuana', 'cocaine', 'heroin', 'drug',
            
            // Scam/fraud related
            'scam', 'fraud', 'hack', 'crack', 'phishing', 'spam',
        ];

        foreach ($inappropriatePatterns as $pattern) {
            if (strpos($lowerUsername, $pattern) !== false) {
                return true;
            }
        }

        // Check for number-based inappropriate usernames
        if (preg_match('/[0-9]{3,}/', $username)) {
            // Check if it looks like a phone number pattern
            if (preg_match('/^(1\d{10}|555\d{7}|800\d{7}|888\d{7}|900\d{7})/', $username)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeContactInfo(string $username): bool
    {
        // Check for email-like patterns
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $username)) {
            return true;
        }

        // Check for phone number patterns
        if (preg_match('/^(1[0-9]{10}|[0-9]{3}[0-9]{3}[0-9]{4}|[0-9]{3}-[0-9]{3}-[0-9]{4})/', $username)) {
            return true;
        }

        // Check for website/domain patterns
        if (preg_match('/[a-zA-Z0-9.-]+\.(com|org|net|edu|gov|mil|int|info|biz|co|io|me|tv|cc)/', $username)) {
            return true;
        }

        return false;
    }
}