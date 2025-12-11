<?php

namespace App\Http\Livewire\Loans;

use App\Models\CommitteeApproval;
use App\Models\Committee;
use App\Models\LoansModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\UserActionLog;

class LoanCommittee extends Component
{
    public function approve($id)
    {
        $loan = LoansModel::findOrFail($id);
        $committee = Committee::where('loan_category', $loan->loan_category)->first();

        if (!$committee) {
            session()->flash('error', 'No committee found for this loan category.');
            return;
        }

        $user = Auth::user();
        $nextApprover = $committee->getNextApprover($id);
        if (!$nextApprover || $nextApprover->id !== $user->id) {
            session()->flash('error', 'You are not the next approver in the sequence.');
            return;
        }

        // Record approval
        CommitteeApproval::create([
            'loan_id' => $loan->id,
            'committee_id' => $committee->id,
            'committee_member_id' => $user->id,
            'decision' => 'APPROVED',
            'reason' => null,
            'status' => 'APPROVED',
        ]);

        // Log user action
        UserActionLog::create([
            'user_id' => $user->id,
            'action_type' => 'LOAN_APPROVED',
            'action_details' => "Loan ID: {$loan->id} approved by {$user->name}",
        ]);

        if ($committee->hasEnoughApprovals($id)) {
            $loan->update(['status' => 'PENDING']);
            $this->sendApproval($id, 'has requested for approval of a new loan', '11');
            session()->flash('message', 'Loan has received enough approvals and is now pending.');
        } else {
            session()->flash('message', 'Approval recorded. Waiting for more approvals.');
        }
    }
} 