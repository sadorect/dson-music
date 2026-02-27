<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('layouts.glass-app')] class extends Component {}; ?>

<div class="max-w-3xl mx-auto px-4 py-12 space-y-6">
    <h1 class="text-3xl font-bold text-gray-800">Terms of Service</h1>
    <p class="text-sm text-gray-400">Last updated: {{ date('F Y') }}</p>

    @foreach([
        ['title' => '1. Acceptance',
         'body'  => 'By using GrinMuzik you agree to these Terms. If you do not agree, do not use the service.'],
        ['title' => '2. Your Account',
         'body'  => 'You are responsible for maintaining the confidentiality of your password and for all activities under your account. You must provide accurate information at registration.'],
        ['title' => '3. Content — Artists',
         'body'  => 'By uploading content to GrinMuzik you confirm that you own or have the necessary rights to that content, that it does not infringe any third-party intellectual property, and that it does not violate any applicable law. You grant GrinMuzik a non-exclusive licence to display and stream your content to users of the platform. You retain ownership of your content.'],
        ['title' => '4. Content — Listeners',
         'body'  => 'Streaming music on GrinMuzik is for personal, non-commercial listening only. You may not download, redistribute, record, or resell any content without the artist\'s explicit permission.'],
        ['title' => '5. Donations & Payments',
         'body'  => 'Donations are processed by Stripe. By making a donation you agree to Stripe\'s Terms of Service. Donations are non-refundable except as required by applicable law or at the artist\'s discretion.'],
        ['title' => '6. Prohibited Conduct',
         'body'  => 'You may not use GrinMuzik to upload illegal, infringing, defamatory, or hateful content; to spam or harass other users; to attempt to gain unauthorised access to the platform or other accounts; or to scrape or crawl the service.'],
        ['title' => '7. Termination',
         'body'  => 'We reserve the right to suspend or terminate accounts that violate these Terms, without prior notice.'],
        ['title' => '8. Disclaimers',
         'body'  => 'GrinMuzik is provided "as is" without warranties of any kind. We are not liable for any indirect, incidental, or consequential damages arising from your use of the service.'],
        ['title' => '9. Changes',
         'body'  => 'We may update these Terms. Continued use of the platform after changes are posted constitutes acceptance of the new Terms.'],
        ['title' => '10. Contact',
         'body'  => 'Questions? Use our Contact page.'],
    ] as $section)
    <div class="glass-card rounded-2xl p-6 space-y-2">
        <h2 class="font-bold text-gray-800">{{ $section['title'] }}</h2>
        <p class="text-sm text-gray-600 leading-relaxed">{{ $section['body'] }}</p>
    </div>
    @endforeach
</div>
