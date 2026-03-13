<?php

use App\Mail\ContactFormMail;
use App\Rules\CaptchaAnswer;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.glass-app')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';
    public string $captcha = '';
    public bool $sent = false;

    public function with(): array
    {
        return [
            'seo' => [
                'title' => 'Contact',
                'description' => 'Contact the GrinMuzik team for support, partnerships, feedback, or artist questions.',
                'canonical' => route('contact'),
                'json_ld' => [
                    [
                        '@context' => 'https://schema.org',
                        '@type' => 'ContactPage',
                        'name' => 'Contact GrinMuzik',
                        'url' => route('contact'),
                        'description' => 'Contact the GrinMuzik team for support, partnerships, feedback, or artist questions.',
                    ],
                ],
            ],
            'contactReasons' => [
                'General support and technical issues',
                'Artist onboarding or payout questions',
                'Partnerships, press, or product feedback',
            ],
        ];
    }

    public function send(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:200'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'captcha' => ['required', 'integer', new CaptchaAnswer],
        ]);

        Mail::to(config('mail.from.address'))
            ->send(new ContactFormMail(
                senderName: $this->name,
                senderEmail: $this->email,
                subject: $this->subject,
                body: $this->message,
            ));

        $this->reset('name', 'email', 'subject', 'message', 'captcha');
        $this->sent = true;
    }
};
?>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="glass-card relative overflow-hidden rounded-[2rem] px-6 py-8 sm:px-8 sm:py-10">
            <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l from-sky-200/30 to-transparent lg:block"></div>

            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1.15fr),minmax(280px,0.85fr)] lg:items-start">
                <div class="max-w-3xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-primary/70">Contact</p>
                    <h1 class="text-3xl font-black tracking-tight text-gray-900 sm:text-5xl">Get in touch with the team behind GrinMuzik.</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">
                        Use the form for support, product feedback, artist questions, partnerships, or anything else that helps us improve the platform.
                    </p>
                </div>

                <div class="rounded-[1.8rem] border border-white/60 bg-white/80 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">Common Reasons</p>
                    <ul class="mt-4 space-y-3 text-sm text-gray-600">
                        @foreach($contactReasons as $reason)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                <span>{{ $reason }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        @if($sent)
            <section class="glass-card rounded-[2rem] p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="mt-4 text-2xl font-black tracking-tight text-gray-900">Message sent</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-gray-600 sm:text-base">Thanks for reaching out. Your message is on its way to the team, and we will respond as soon as possible.</p>
                <button wire:click="$set('sent', false)"
                        class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                    Send another message
                </button>
            </section>
        @else
            <section class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr),minmax(260px,0.7fr)]">
                <div class="glass-card rounded-[2rem] p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-black tracking-tight text-gray-900">Send a message</h2>
                        <p class="mt-2 text-sm text-gray-500">The more detail you give us, the faster we can help.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="ct-name" value="Your Name" />
                            <x-text-input wire:model="name" id="ct-name" class="mt-1 block w-full" type="text" placeholder="Jane Smith" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="ct-email" value="Email Address" />
                            <x-text-input wire:model="email" id="ct-email" class="mt-1 block w-full" type="email" placeholder="jane@example.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-input-label for="ct-subject" value="Subject" />
                        <x-text-input wire:model="subject" id="ct-subject" class="mt-1 block w-full" type="text" placeholder="What do you need help with?" />
                        <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                    </div>

                    <div class="mt-5">
                        <x-input-label for="ct-message" value="Message" />
                        <textarea wire:model="message"
                                  id="ct-message"
                                  rows="7"
                                  class="mt-1 block w-full rounded-2xl border-gray-300 bg-white/70 text-sm focus:border-primary focus:ring-primary"
                                  placeholder="Tell us what is going on, what page you were on, and what outcome you expected..."></textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-1" />
                    </div>

                    <div class="mt-5">
                        <x-math-captcha wire:model="captcha" error-bag="captcha" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button wire:click="send"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                            <span wire:loading.remove wire:target="send">Send Message</span>
                            <span wire:loading wire:target="send">Sending...</span>
                        </button>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="glass-card rounded-[1.8rem] p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">Best For</p>
                        <h3 class="mt-3 text-lg font-black tracking-tight text-gray-900">Support, feedback, and partnerships</h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">If your issue relates to artist uploads, donations, profile setup, or site bugs, include the relevant page and as much context as possible.</p>
                    </div>

                    <div class="glass-card rounded-[1.8rem] p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary/70">Response Flow</p>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">Messages are delivered to the platform inbox email configured for GrinMuzik. For urgent product issues, sending one detailed message is better than sending several short ones.</p>
                    </div>
                </aside>
            </section>
        @endif
    </div>
</div>
