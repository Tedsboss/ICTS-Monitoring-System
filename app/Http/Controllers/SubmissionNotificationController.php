<?php

namespace App\Http\Controllers;

use App\Models\SubmissionNotification;
use Illuminate\Http\Request;

class SubmissionNotificationController extends Controller
{
  public function index()
  {
    $query = $this->visibleNotifications();

    $notifications = (clone $query)
      ->with(['agency', 'form', 'upliftMeasure'])
      ->latest()
      ->limit(10)
      ->get();

    $unreadCount = (clone $query)
      ->whereNull('read_at')
      ->count();

    return response()->json([
      'unread_count' => $unreadCount,
      'notifications' => $notifications->map(function ($notification) {
        return [
          'id' => $notification->id,
          'title' => $notification->title,
          'message' => $notification->message,
          'action' => $notification->action,
          'remarks' => $notification->remarks,
          'submission_type' => $notification->submission_type,
          'agency' => optional($notification->agency)->display_name,
          'form' => optional($notification->form)->title ?? optional($notification->upliftMeasure)->title,
          'is_read' => $notification->read_at != null,
          'created_at' => optional($notification->created_at)->diffForHumans(),
          'url' => $this->notificationUrl($notification),
        ];
      }),
    ]);
  }

  public function read(Request $request, SubmissionNotification $notification)
  {
    abort_unless($this->canSee($notification), 403);

    if ($notification->read_at == null) {
      $notification->update(['read_at' => now()]);
    }

    return response()->json(['status' => 'ok']);
  }

  private function visibleNotifications()
  {
    return SubmissionNotification::query()
      ->when(!auth()->user()->isSuperAdmin(), function ($query) {
        $query->where(function ($query) {
          $query->where('recipient_user_id', auth()->id())
            ->orWhere(function ($query) {
              $query->whereNull('recipient_user_id')
                ->where('agency_id', auth()->user()->agency_id);
            });
        });
      });
  }

  private function canSee(SubmissionNotification $notification): bool
  {
    return auth()->user()->isSuperAdmin()
      || $notification->recipient_user_id == auth()->id()
      || ($notification->recipient_user_id == null && $notification->agency_id == auth()->user()->agency_id);
  }

  private function notificationUrl(SubmissionNotification $notification): string
  {
    if ($notification->submission_type === 'uplift' && $notification->uplift_submission_id != null) {
      return route('uplift-submissions.show', $notification->uplift_submission_id);
    }

    return route('submissions.show', $notification->form_submission_id);
  }
}
