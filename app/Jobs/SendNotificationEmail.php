<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user to send the notification to.
     */
    public User $user;

    /**
     * The notification subject.
     */
    public string $subject;

    /**
     * The notification message.
     */
    public string $message;

    /**
     * Additional data for the notification.
     */
    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $subject, string $message, array $data = [])
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::raw($this->message, function ($mail) {
                $mail->to($this->user->email)
                    ->subject($this->subject);
            });

            Log::info('Notification email sent', [
                'user_id' => $this->user->id,
                'subject' => $this->subject
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification email', [
                'user_id' => $this->user->id,
                'subject' => $this->subject,
                'error' => $e->getMessage()
            ]);
            
            // Retry the job up to 3 times
            if ($this->attempts() < 3) {
                $this->release(60); // Retry after 60 seconds
            }
        }
    }
}
