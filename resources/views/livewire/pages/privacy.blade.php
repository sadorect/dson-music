<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.glass-app')] class extends Component {}; ?>

<div class="max-w-3xl mx-auto px-4 py-12 space-y-6">
    <h1 class="text-3xl font-bold text-gray-800">Privacy Policy</h1>
    <p class="text-sm text-gray-400">Last updated: {{ date('F Y') }}</p>

    @foreach([
        ['title' => '1. Information We Collect',
         'body'  => 'We collect information you provide when you register (name, email address, password). If you are an artist, we also collect your stage name, bio, and uploaded media. If you make a donation, payment is processed by Stripe — we do not store card details. We automatically collect basic usage data such as IP addresses, browser type, pages visited, and play history for logged-in users.'],
        ['title' => '2. How We Use Your Information',
         'body'  => 'We use your information to operate the platform (authentication, playing music, processing donations), send transactional emails (password resets, email verification), personalise your experience (play history, liked tracks), and improve the service. We do not sell your personal data.'],
        ['title' => '3. Cookies',
         'body'  => 'We use cookies to maintain your session and remember preferences. We do not use third-party advertising cookies. You may disable cookies in your browser settings but this will prevent you from logging in.'],
        ['title' => '4. Third Parties',
         'body'  => 'Payments are processed by Stripe. Please review Stripe\'s privacy policy at stripe.com/privacy. Media files may be stored on Amazon S3 or similar cloud storage providers. We do not share your data with any other third parties for marketing purposes.'],
        ['title' => '5. Data Retention',
         'body'  => 'We retain your account data as long as your account is active. You may request deletion of your account and associated data by contacting us. Anonymised play count statistics may be retained permanently.'],
        ['title' => '6. Your Rights',
         'body'  => 'Depending on your jurisdiction, you may have the right to access, correct, or delete your personal data, object to processing, and request portability. Contact us to exercise these rights.'],
        ['title' => '7. Security',
         'body'  => 'We implement industry-standard security measures including password hashing (bcrypt), encrypted connections (HTTPS), and access controls. No system is 100% secure; please use a strong, unique password.'],
        ['title' => '8. Contact',
         'body'  => 'Questions about this policy? Contact us via the Contact page.'],
    ] as $section)
    <div class="glass-card rounded-2xl p-6 space-y-2">
        <h2 class="font-bold text-gray-800">{{ $section['title'] }}</h2>
        <p class="text-sm text-gray-600 leading-relaxed">{{ $section['body'] }}</p>
    </div>
    @endforeach
</div>
