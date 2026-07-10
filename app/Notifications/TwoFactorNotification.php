<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class TwoFactorNotification extends Notification implements ShouldQueue
{
  use Queueable;

  private $user = null;
  private $device_details = [];

  /**
   * Create a new notification instance.
   */
  public function __construct(User $user, $device_details)
  {
    $this->user = $user;
    $this->device_details = $device_details;
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
    return (new MailMessage)
      ->subject('[OTP] ICC-Portal : ' . $this->user->twofactorcode)
      ->greeting('Hi ' . $this->user->full_name . ',')
      ->line(new HtmlString("Here is your One-Time Password (OTP) to log in to your account : <strong>{$this->user->twofactorcode}</strong><br>"))
      ->line(new HtmlString("This OTP is valid for <strong>5 minutes</strong>. For your security, please do not share this code with anyone. We noticed this login request from the following details:<br><ul><li>Device : {$this->device_details[0]}</li><li>IP : {$this->device_details[1]}</li></ul>"))
      ->line('If you did not request this login, please change your password immediately.')
      ->salutation(new HtmlString('<br>Thanks,<br>ICC Secretariat'));
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
}
