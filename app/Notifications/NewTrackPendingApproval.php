<?php

namespace App\Notifications;

use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewTrackPendingApproval extends Notification
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
            ->subject('New Track Pending Approval')
            ->line('A new track has been uploaded and requires your approval.')
            ->line('Track Title: ' . $this->track->title)
            ->line('Artist: ' . $this->track->artist->artist_name)
            ->action('Review Track', route('admin.tracks.review.index', $this->track))
            ->line('Please review the track for quality and content guidelines.');
    }

    public function toArray($notifiable)
    {
        return [
            'track_id' => $this->track->id,
            'track_title' => $this->track->title,
            'artist_name' => $this->track->artist->artist_name,
            'uploaded_at' => $this->track->created_at
        ];
    }
}
