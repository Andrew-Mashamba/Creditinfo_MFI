<?php

namespace App\Services;

use App\Models\ApprovalAction;

class ManagementApproval
{


    public static function checkApprovalStatus($loan_id, $approver_id)
    {

        $checkAction = ApprovalAction::where('loan_id', $loan_id)->where('approver_id', $approver_id)->first();
        if ($checkAction) {
            return $checkAction->status;
        } else {

            return 'PENDING';
        }
    }

    function countApprovers($max, $loan_id)
    {
        $count = ApprovalAction::where('loan_id', $loan_id)->count();

        if ($count == $max) {
            return true;
        } else {
            return false;
        }
    }
}
