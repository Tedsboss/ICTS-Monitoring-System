<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\SubmitOrderOfPayment;

trait NotificationTrait
{    
    public function sendOrderOfPaymentEmail(string $changeType, $model, ?string $remarks = null)
    {
        $recipients = match ($changeType) {
            'Reverted' => $model->created_by ? [User::find($model->created_by)] : [], 
            'Submitted' => empty($model->reviewer_id) ? User::where('role_id', 8)->get() : [User::find($model->reviewer_id)],
            // 'For Approval' => User::where('role_id', 9)->get(), 
            'For Approval' =>  empty($model->approver_id) ? User::where('role_id', 9)->get() : [User::find($model->approver_id)], 
            'Approved' => $model->created_by ? [User::find($model->created_by)] : [],
            default => collect(), 
        };

        foreach ($recipients as $recipient) {
            if ($recipient) { 
                $recipient->notify(new SubmitOrderOfPayment($model, $remarks, $changeType));
            }
        }

    }
}
