<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function with(): array
    {
        return [
            'seo' => [
                'title' => 'Privacy Policy',
                'description' => 'Read the GrinMuzik privacy policy covering accounts, payments, cookies, data handling, and your rights.',
                'canonical' => route('privacy'),
            ],
            'sections' => [
                ['title' => 'Information we collect', 'body' => 'We collect the information you provide when registering, building an artist profile, liking tracks, creating playlists, and contacting us. Payment details for donations are processed by Stripe rather than stored directly by GrinMuzik.'],
                ['title' => 'How we use your information', 'body' => 'We use account and activity data to operate the platform, provide playback and discovery features, process support requests, and improve the product experience.'],
                ['title' => 'Cookies and sessions', 'body' => 'Cookies are used for authentication, sessions, and core product functionality. Disabling them may prevent sign-in and other essential features from working properly.'],
                ['title' => 'Third-party services', 'body' => 'Stripe handles payment processing. Cloud storage and infrastructure providers may also be used to host uploaded media and site services.'],
                ['title' => 'Retention and deletion', 'body' => 'We retain account data while your account remains active unless deletion is requested or legal obligations require otherwise. Aggregated non-identifying platform statistics may be retained longer.'],
                ['title' => 'Your rights', 'body' => 'Depending on your jurisdiction, you may have rights to access, correct, export, or delete your personal data. Contact us if you need help exercising those rights.'],
                ['title' => 'Security', 'body' => 'We use common security practices such as hashed passwords, HTTPS, and access controls, but no internet service can guarantee absolute security.'],
            ],
        ];
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <section class="glass-card rounded-[2rem] px-6 py-8 sm:px-8 sm:py-10">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Privacy Policy</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">How GrinMuzik handles your data.</h1>
            <p class="mt-4 max-w-3xl text-sm leading-relaxed text-gray-600 sm:text-base">
                This policy explains what information we collect, how it is used, and what rights you may have when using the platform.
            </p>
            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Last updated {{ date('F Y') }}</p>
        </section>

        <section class="space-y-4">
            @foreach($sections as $index => $section)
                <article class="glass-card rounded-[1.8rem] p-6">
                    <div class="flex items-start gap-4">
                        <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-black text-primary-700">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <h2 class="text-lg font-black tracking-tight text-gray-900">{{ $section['title'] }}</h2>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600 sm:text-base">{{ $section['body'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="glass-card rounded-[2rem] p-6 sm:p-8">
            <h2 class="text-2xl font-black tracking-tight text-gray-900">Questions about privacy?</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-600 sm:text-base">
                If you need clarification about how your information is handled, or if you want to request access, correction, or deletion, contact the team directly.
            </p>
            <a href="{{ route('contact') }}"
               wire:navigate
               class="mt-5 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                Contact Us
            </a>
        </section>
    </div>
</div>
