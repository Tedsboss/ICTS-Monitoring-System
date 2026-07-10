<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ReplyInquiryNotification extends Notification implements ShouldQueue
{
  use Queueable;

  private $inquiry;
  private $reply_message;

  /**
   * Create a new notification instance.
   */
  public function __construct(Inquiry $inquiry, $reply_message)
  {
    $this->inquiry = $inquiry;
    $this->reply_message = $reply_message;
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
      ->subject('[Reply] ICC Portal - User Inquiry')
      ->greeting(" ") // Remove the default greeting
      ->line(new HtmlString($this->reply_message))
      ->salutation(" "); // Remove the default salutation
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
