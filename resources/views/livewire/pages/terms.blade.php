<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public function with(): array
    {
        return [
            'seo' => [
                'title' => 'Terms of Service',
                'description' => 'Read the GrinMuzik terms covering accounts, uploaded content, donations, prohibited conduct, and service use.',
                'canonical' => route('terms'),
            ],
            'sections' => [
                ['title' => 'Acceptance', 'body' => 'By using GrinMuzik, you agree to these terms. If you do not agree, you should not use the platform.'],
                ['title' => 'Your account', 'body' => 'You are responsible for your credentials, the accuracy of your registration details, and activity that occurs under your account.'],
                ['title' => 'Artist content', 'body' => 'Artists must only upload content they own or are authorized to use. Uploading content grants GrinMuzik a non-exclusive right to display and stream that content through the service.'],
                ['title' => 'Listener use', 'body' => 'Streaming through GrinMuzik is for personal, non-commercial listening unless you have explicit permission from the rights holder.'],
                ['title' => 'Donations and payments', 'body' => 'Payments are processed by Stripe. Donation unlocks are generally non-refundable unless required by law or otherwise agreed.'],
                ['title' => 'Prohibited conduct', 'body' => 'Users may not upload unlawful or infringing material, abuse the platform, harass others, attempt unauthorized access, or misuse the service through scraping or spam.'],
                ['title' => 'Termination', 'body' => 'Accounts may be suspended or removed for violating these terms or harming the platform or other users.'],
                ['title' => 'Disclaimers and changes', 'body' => 'The service is provided as-is. We may update the terms over time, and continued use after changes means you accept the updated version.'],
            ],
        ];
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <section class="glass-card rounded-[2rem] px-6 py-8 sm:px-8 sm:py-10">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Terms of Service</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">The rules that govern use of GrinMuzik.</h1>
            <p class="mt-4 max-w-3xl text-sm leading-relaxed text-gray-600 sm:text-base">
                These terms explain the responsibilities of listeners, artists, and the platform when using GrinMuzik.
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
            <h2 class="text-2xl font-black tracking-tight text-gray-900">Need clarification on these terms?</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-600 sm:text-base">
                If something here is unclear, reach out before relying on assumptions, especially if the issue affects rights, payments, or uploaded content.
            </p>
            <a href="{{ route('contact') }}"
               wire:navigate
               class="mt-5 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                Contact Us
            </a>
        </section>
    </div>
</div>
