<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use App\Rules\CaptchaAnswer;

new #[Layout('layouts.glass-app')] class extends Component
{
    public string $name    = '';
    public string $email   = '';
    public string $subject = '';
    public string $message = '';
    public string $captcha = '';
    public bool   $sent    = false;

    public function send(): void
    {
        $this->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:200'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'captcha' => ['required', 'integer', new CaptchaAnswer],
        ]);

        Mail::to(config('mail.from.address'))
            ->send(new ContactFormMail(
                senderName:  $this->name,
                senderEmail: $this->email,
                subject:     $this->subject,
                body:        $this->message,
            ));

        $this->reset('name', 'email', 'subject', 'message', 'captcha');
        $this->sent = true;
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 py-12 space-y-8">

    <div class="text-center space-y-3">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Contact Us</h1>
        <p class="text-gray-500">Questions, feedback, or partnership enquiries — we'd love to hear from you.</p>
    </div>

    @if($sent)
    <div class="glass-card rounded-2xl p-8 text-center space-y-3">
        <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto">
            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-800">Message sent!</h2>
        <p class="text-sm text-gray-500">Thanks for reaching out. We'll get back to you as soon as possible.</p>
        <button wire:click="$set('sent', false)" class="text-sm text-red-500 hover:text-red-700 font-medium transition">Send another message</button>
    </div>
    @else
    <div class="glass-card rounded-2xl p-7 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
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
        <div>
            <x-input-label for="ct-subject" value="Subject" />
            <x-text-input wire:model="subject" id="ct-subject" class="mt-1 block w-full" type="text" placeholder="What's this about?" />
            <x-input-error :messages="$errors->get('subject')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="ct-message" value="Message" />
            <textarea wire:model="message" id="ct-message" rows="6"
                      class="mt-1 block w-full rounded-xl border-gray-300 bg-white/70 focus:border-red-400 focus:ring-red-400 text-sm"
                      placeholder="Tell us what's on your mind…"></textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-1" />
        </div>
        <x-math-captcha wire:model="captcha" error-bag="captcha" />
        <div class="flex justify-end">
            <button wire:click="send" wire:loading.attr="disabled"
                    class="glass-btn-primary glass-btn-primary-hover px-6 py-2.5 rounded-xl font-semibold text-sm flex items-center gap-2">
                <span wire:loading.remove wire:target="send">Send Message</span>
                <span wire:loading wire:target="send">Sending…</span>
            </button>
        </div>
    </div>
    @endif

</div>
