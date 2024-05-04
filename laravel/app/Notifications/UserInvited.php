<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserInvited extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $role, public User $sender)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        $url = $this->generateInvitationUrl($notifiable->routes['mail']);

        return (new MailMessage)
            ->subject('Personal Invitation')
            ->greeting('Hello!')
            ->line("You have been invited to join the {$appName} application!")
            ->action('Click here to register your account', url($url))
            ->line('Note: this link expires after 24 hours.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Generates a unique signed URL that the mail receiver can user to register.
     * The URL contains the UserLevel and the receiver's email address, and will be valid for 1 day.
     *
     * @param  $notifiable
     * @return string
     */
    public function generateInvitationUrl(string $email)
    {
        return URL::temporarySignedRoute('register.create', now()->addDay(), [
            'role' => $this->role,
            'email' => $email,
        ]);
    }
}
