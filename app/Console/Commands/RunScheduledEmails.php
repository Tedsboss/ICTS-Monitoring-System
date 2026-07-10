<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\ScheduledEmail;
use App\Models\ScheduledEmailHistory;
use App\Models\User;
use App\Notifications\ScheduledEmailNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class RunScheduledEmails extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:run-scheduled-emails';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Run all scheduled emails';

  /**
   * Create a new command instance.
   *.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $agency_heads = Agency::whereNotNull('head_email')->get(['head_email', 'id', 'head_designation', 'head_firstname', 'head_lastname'])->toArray();
    $agency_staffs = User::where('emailnotif', 'Y')->where('agency_id', '!=', 276)->get(['email', 'agency_id', 'designation', 'firstname', 'lastname'])->toArray();
    $neda_pis = User::where('emailnotif', 'Y')->whereHas('role.permissions', function ($query) {
      $query->whereIn('id', [29, 30]);
    })->where('agency_id', 276)->where('staff_id', 19)->get(['email', 'agency_id', 'designation', 'firstname', 'lastname'])->toArray();
    $neda_other_ss = User::where('emailnotif', 'Y')->whereHas('role.permissions', function ($query) {
      $query->whereIn('id', [29, 30]);
    })->where('agency_id', 276)->where('staff_id', '!=', 19)->get(['email', 'agency_id', 'designation', 'firstname', 'lastname'])->toArray();
    $neda_admin = User::where('emailnotif', 'Y')->whereHas('role.permissions', function ($query) {
      $query->whereIn('id', [25]);
    })->where('agency_id', 276)->get(['email', 'agency_id', 'designation', 'firstname', 'lastname'])->toArray();

    // dd($neda_admin);
    $scheduledemails = ScheduledEmail::where('status', 1)->get();
    $hit = 0;
    foreach ($scheduledemails as $scheduledemail) {
      $hit = 1;
      $emailCreatedAt = Carbon::parse($scheduledemail->start_date)->startOfMinute();
      $currentTime = Carbon::now()->startOfMinute();
      $time = explode(':', Carbon::parse($scheduledemail->start_date)->format('H:i:s'));
      // if ($scheduledemail->type == 'One Time') {
      //   if ($emailCreatedAt->equalTo($currentTime)) {
      //     $hit = 1;
      //   }
      // } else if ($scheduledemail->type == 'Daily') {
      //   if ($emailCreatedAt->lessThanOrEqualTo($currentTime)) {
      //     if (Carbon::now()->setTime($time[0], $time[1], $time[2])->startOfMinute()->equalTo($currentTime)) {
      //       $hit = 1;
      //     }
      //   }
      // } else if ($scheduledemail->type == 'Weekly') {
      //   if ($emailCreatedAt->lessThanOrEqualTo($currentTime)) {
      //     if (Carbon::now()->startOfWeek()->addDay($scheduledemail->weeks_on - 1)->setTime($time[0], $time[1], $time[2])->startOfMinute()->equalTo($currentTime)) {
      //       $hit = 1;
      //     }
      //   }
      // } else if ($scheduledemail->type == 'Monthly') {
      //   if ($emailCreatedAt->lessThanOrEqualTo($currentTime)) {
      //     if ($scheduledemail->months_on == 'First Monday') {
      //       if (Carbon::now()->firstOfMonth(Carbon::MONDAY)->setTime($time[0], $time[1], $time[2])->startOfMinute()->equalTo($currentTime)) {
      //         $hit = 1;
      //       }
      //     } else if ($scheduledemail->months_on == 'Last Friday') {
      //       if (Carbon::now()->lastOfMonth(Carbon::FRIDAY)->setTime($time[0], $time[1], $time[2])->startOfMinute()->equalTo($currentTime)) {
      //         $hit = 1;
      //       }
      //     } else {
      //       if (Carbon::now()->setDate($currentTime->year, $currentTime->month, $scheduledemail->months_on)->setTime($time[0], $time[1], $time[2])->startOfMinute()->equalTo($currentTime)) {
      //         $hit = 1;
      //       }
      //     }
      //   }












      //   $emailCreatedAt = Carbon::parse($scheduledemail->start_date)->format('H:i');
      //   $currentTime = Carbon::now()->format('H:i');
      //   if ($scheduledemail->months_on == 'First Monday') {
      //     $today = Carbon::today();
      //     $firstMonday = Carbon::parse('first monday of this month');
      //     if ($today->isSameDay($firstMonday) && $emailCreatedAt === $currentTime) {
      //       Notification::route('mail', $recipient_emails)->notify(new ScheduledEmailNotification($scheduledemail));
      //     }
      //   } else if ($scheduledemail->months_on == 'First Monday') {
      //     $today = Carbon::today();
      //     $lastFriday = Carbon::parse('last friday of this month');
      //     if ($today->isSameDay($lastFriday) && $emailCreatedAt === $currentTime) {
      //       Notification::route('mail', $recipient_emails)->notify(new ScheduledEmailNotification($scheduledemail));
      //     }
      //   } else {
      //     $emailDay = $scheduledemail->months_on;
      //     $currentDay = Carbon::now()->day;
      //     if ($emailDay === $currentDay && $emailCreatedAt === $currentTime) {
      //       Notification::route('mail', $recipient_emails)->notify(new ScheduledEmailNotification($scheduledemail));
      //     }
      //   }
      // }

      if ($hit == 1) {
        $recipients = [];
        foreach ($scheduledemail->recipients as $recipient) {
          if ($recipient->id == 1) {
            $recipients = array_merge($recipients, $agency_heads);
          } else if ($recipient->id == 2) {
            $recipients = array_merge($recipients, $agency_staffs);
          } else if ($recipient->id == 3) {
            $recipients = array_merge($recipients, $neda_pis);
          } else if ($recipient->id == 4) {
            $recipients = array_merge($recipients, $neda_other_ss);
          } else if ($recipient->id == 5) {
            $recipients = array_merge($recipients, $neda_admin);
          }
        }
        foreach ($recipients as $recipient) {
          $recipient = array_values($recipient);
          $cc_recipients_email = [];
          if ($scheduledemail->group == 'By Agency') {
            if ($scheduledemail->cc_recipients->first()->id == 1) {
              $cc_recipients_email = collect($agency_heads)->map(function ($item) {
                return config('mail.email_prefix') . $item[0];
              })->toArray();
            } else {
              $cc_recipients_email = collect($agency_staffs)->map(function ($item) {
                return config('mail.email_prefix') . $item[0];
              })->toArray();
            }
          }
          Notification::route('mail', config('mail.email_prefix') . $recipient[0])->notify(new ScheduledEmailNotification($scheduledemail, $cc_recipients_email, $recipient[3], $recipient[4], $recipient[2])); // Parameters: Model, CC, Firstname, Lastname, Designation
        }

        $scheduledemail_history = new ScheduledEmailHistory();
        $scheduledemail_history->scheduled_email_id = $scheduledemail->id;
        $scheduledemail_history->save();
      }
    }
  }
}
