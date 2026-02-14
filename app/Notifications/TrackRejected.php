<?php

namespace App\Notifications;

use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrackRejected extends Notification
{
    use Queueable;

    protected $track;

    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Track Review Update')
            ->greeting('Hello!')
            ->line('Your track "'.$this->track->title.'" requires some adjustments.')
            ->line('Reason: '.$this->track->rejection_reason)
            ->action('Edit Track', route('artist.tracks.edit', $this->track))
            ->line('You can make the necessary changes and submit for review again.');
    }

    public function toArray($notifiable)
    {
        return [
            'track_id' => $this->track->id,
            'track_title' => $this->track->title,
            'rejection_reason' => $this->track->rejection_reason,
            'rejected_at' => $this->track->updated_at,
        ];
    }
}
