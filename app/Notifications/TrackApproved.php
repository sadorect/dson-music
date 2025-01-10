<?php

namespace App\Notifications;

use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TrackApproved extends Notification
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
            ->subject('Your Track Has Been Approved!')
            ->greeting('Great news!')
            ->line('Your track "' . $this->track->title . '" has been approved.')
            ->line('It is now available for public access.')
            ->action('View Track', route('artist.tracks.show', $this->track))
            ->line('Keep creating amazing music!');
    }

    public function toArray($notifiable)
    {
        return [
            'track_id' => $this->track->id,
            'track_title' => $this->track->title,
            'approved_at' => $this->track->approved_at,
            'approved_by' => $this->track->approved_by
        ];
    }
}
