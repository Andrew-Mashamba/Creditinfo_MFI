<?php

namespace App\Jobs;

use App\Mail\AccountInformationEmail;
use App\Models\User;
use App\Models\AccountsModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAccountInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected $member;
    protected $processId;

    /**
     * Create a new job instance.
     */
    public function __construct(User $member)
    {
        $this->member = $member;
        $this->processId = uniqid('acc_info_', true);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting account information notification', [
            'process_id' => $this->processId,
            'member_id' => $this->member->id,
            'email' => $this->member->email
        ]);

        try {
            // Get member's accounts
            $sharesAccount = AccountsModel::where('member_id', $this->member->id)
                ->where('account_type', 'shares')
                ->first();

            $savingsAccount = AccountsModel::where('member_id', $this->member->id)
                ->where('account_type', 'savings')
                ->first();

            $depositsAccount = AccountsModel::where('member_id', $this->member->id)
                ->where('account_type', 'deposits')
                ->first();

            Log::info('Retrieved member accounts', [
                'process_id' => $this->processId,
                'has_shares' => !is_null($sharesAccount),
                'has_savings' => !is_null($savingsAccount),
                'has_deposits' => !is_null($depositsAccount)
            ]);

            // Send account information email
            Mail::to($this->member->email)
                ->send(new AccountInformationEmail(
                    $this->member->first_name . ' ' . $this->member->last_name,
                    $sharesAccount,
                    $savingsAccount,
                    $depositsAccount
                ));

            Log::info('Account information email sent successfully', [
                'process_id' => $this->processId,
                'member_id' => $this->member->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send account information email', [
                'process_id' => $this->processId,
                'member_id' => $this->member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Account information notification failed', [
            'process_id' => $this->processId,
            'member_id' => $this->member->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
} 