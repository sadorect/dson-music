<?php

namespace App\Notifications;

use App\Models\ArtistProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ArtistVerified extends Notification
{
    use Queueable;

    protected $artist;

    public function __construct(ArtistProfile $artist)
    {
        $this->artist = $artist;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Artist Profile is Now Verified')
            ->line('Congratulations! Your artist profile has been verified.')
            ->line('You now have access to additional features and improved visibility.')
            ->action('View Your Profile', route('artist.dashboard'))
            ->line('Thank you for being part of DSON Music!');
    }

    public function toArray($notifiable): array
    {
        return [
            'artist_id' => $this->artist->id,
            'message' => 'Your artist profile has been verified'
        ];
    }
}
