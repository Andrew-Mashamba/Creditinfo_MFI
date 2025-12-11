<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\sub_products;
use App\Models\MandatorySavingsTracking;
use App\Models\MandatorySavingsNotification;
use App\Models\MandatorySavingsSettings;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\MandatorySavingsPaymentNotification;
use Illuminate\Support\Facades\Mail;
//use App\Services\NotificationService;

class MandatorySavingsService
{
    protected $settings;

    protected $generatedControlNumbers = [];

    public function __construct()
    {
        // Don't load settings immediately to avoid database access during migration
        $this->settings = null;
    }

    /**
     * Get settings with lazy loading
     */
    protected function getSettings()
    {
        if (!$this->settings) {
            try {
                $this->settings = MandatorySavingsSettings::forInstitution('1');
            } catch (\Exception $e) {
                // Settings not available yet, will be handled in methods that need them
                return null;
            }
        }
        return $this->settings;
    }

    /**
     * Get mandatory savings amount from institution and sub_products.
     */
    public function getMandatorySavingsAmount()
    {
        try {
            // Get institution with ID = 1
            $institution = Institution::find(1);
            if (!$institution || !$institution->mandatory_savings_account) {
                throw new \Exception('Mandatory savings account not configured in institution settings.');
            }

            // Get the mandatory savings product
            $product = sub_products::where('product_account', $institution->mandatory_savings_account)->first();
            if (!$product) {
                throw new \Exception('Mandatory savings product not found.');
            }

            return [
                'amount' => $product->min_balance,
                'account_number' => $institution->mandatory_savings_account,
                'product_id' => $product->sub_product_id,
                'product_name' => $product->product_name
            ];
        } catch (\Exception $e) {
            Log::error('Error getting mandatory savings amount: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create default mandatory savings settings if none exist.
     */
    protected function createDefaultSettings()
    {
        try {
            $this->settings = MandatorySavingsSettings::create([
                'institution_id' => '1',
                'monthly_amount' => 10000, // Default TZS 10,000
                'due_day' => 15, // Due on 15th of each month
                'grace_period_days' => 5, // 5 days grace period
                'enable_notifications' => true,
                'first_reminder_days' => 7, // 7 days before due
                'second_reminder_days' => 3, // 3 days before due
                'final_reminder_days' => 1, // 1 day before due
                'enable_sms_notifications' => false,
                'enable_email_notifications' => false,
                'sms_template' => 'Dear {member_name}, your mandatory savings payment of TZS {amount} for {period} is due on {due_date}.',
                'email_template' => 'Dear {member_name}, your mandatory savings payment of TZS {amount} for {period} is due on {due_date}.'
            ]);
            
            Log::info('Default mandatory savings settings created');
        } catch (\Exception $e) {
            Log::error('Error creating default mandatory savings settings: ' . $e->getMessage());
            throw new \Exception('Failed to create default mandatory savings settings. Please configure settings manually.');
        }
    }

    /**
     * Generate tracking records for all members for a specific month.
     */
    public function generateTrackingRecords($year, $month)
    {

        //dd($year, $month);
        $startTime = microtime(true);
        $processId = uniqid('mandatory_savings_', true);
        
        Log::info('Starting mandatory savings tracking records generation', [
            'process_id' => $processId,
            'year' => $year,
            'month' => $month,
            'timestamp' => now()->toISOString()
        ]);

        try {
            DB::beginTransaction();

            // Step 1: Get mandatory savings configuration
            Log::info('Step 1: Retrieving mandatory savings configuration', [
                'process_id' => $processId,
                'step' => 'configuration_retrieval'
            ]);

            $mandatorySavings = $this->getMandatorySavingsAmount();
            
            Log::info('Mandatory savings configuration retrieved', [
                'process_id' => $processId,
                'amount' => $mandatorySavings['amount'],
                'account_number' => $mandatorySavings['account_number'],
                'product_id' => $mandatorySavings['product_id'],
                'product_name' => $mandatorySavings['product_name']
            ]);

            // Step 2: Check and create settings if needed
            Log::info('Step 2: Validating mandatory savings settings', [
                'process_id' => $processId,
                'step' => 'settings_validation'
            ]);

            if (!$this->getSettings()) {
                Log::warning('No mandatory savings settings found, creating default settings', [
                    'process_id' => $processId,
                    'institution_id' => '1'
                ]);
                $this->createDefaultSettings();
            }
            
            $dueDate = $this->getSettings()->getDueDate($year, $month);
            
            Log::info('Due date calculated', [
                'process_id' => $processId,
                'due_date' => $dueDate->toDateString(),
                'settings_id' => $this->getSettings()->id ?? 'default'
            ]);

            // Step 3: Get active members
            Log::info('Step 3: Retrieving active members', [
                'process_id' => $processId,
                'step' => 'member_retrieval'
            ]);

            $members = ClientsModel::where('status', 'ACTIVE')->get();
            
            Log::info('Active members retrieved', [
                'process_id' => $processId,
                'total_members' => $members->count(),
                'member_numbers' => $members->pluck('client_number')->toArray()
            ]);

            $createdCount = 0;
            $updatedCount = 0;
            $errorCount = 0;
            $notificationSuccessCount = 0;
            $notificationErrorCount = 0;
            $billingSuccessCount = 0;
            $billingErrorCount = 0;

            $errors = [];
            $memberProcessingDetails = [];

            // Step 4: Process each member
            Log::info('Step 4: Processing individual members', [
                'process_id' => $processId,
                'step' => 'member_processing',
                'total_members' => $members->count()
            ]);

            foreach ($members as $index => $member) {
                $memberStartTime = microtime(true);
                $memberProcessId = $processId . '_member_' . $member->client_number;
                
                Log::info('Processing member', [
                    'process_id' => $memberProcessId,
                    'member_number' => $member->client_number,
                    'member_name' => $member->first_name . ' ' . $member->last_name,
                    'progress' => ($index + 1) . '/' . $members->count(),
                    'step' => 'individual_member_processing'
                ]);

                try {
                    // Check if tracking record already exists
                    $existingRecord = MandatorySavingsTracking::where('client_number', $member->client_number)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->first();

                    if ($existingRecord) {
                        Log::info('Existing tracking record found', [
                            'process_id' => $memberProcessId,
                            'tracking_id' => $existingRecord->id,
                            'current_required_amount' => $existingRecord->required_amount,
                            'new_required_amount' => $mandatorySavings['amount']
                        ]);

                        // Update existing record if needed
                        if ($existingRecord->required_amount != $mandatorySavings['amount']) {
                            $oldAmount = $existingRecord->required_amount;
                            $existingRecord->update([
                                'required_amount' => $mandatorySavings['amount'],
                                'balance' => $mandatorySavings['amount'] - $existingRecord->paid_amount
                            ]);
                            $updatedCount++;
                            
                            Log::info('Tracking record updated', [
                                'process_id' => $memberProcessId,
                                'tracking_id' => $existingRecord->id,
                                'old_amount' => $oldAmount,
                                'new_amount' => $mandatorySavings['amount'],
                                'paid_amount' => $existingRecord->paid_amount,
                                'new_balance' => $mandatorySavings['amount'] - $existingRecord->paid_amount
                            ]);
                        } else {
                            Log::info('Tracking record already up to date', [
                                'process_id' => $memberProcessId,
                                'tracking_id' => $existingRecord->id
                            ]);
                        }
                    } else {
                        Log::info('Creating new tracking record', [
                            'process_id' => $memberProcessId,
                            'member_number' => $member->client_number,
                            'required_amount' => $mandatorySavings['amount']
                        ]);

                        // Create new tracking record
                        $newRecord = MandatorySavingsTracking::create([
                            'client_number' => $member->client_number,
                            'account_number' => $mandatorySavings['account_number'],
                            'year' => $year,
                            'month' => $month,
                            'required_amount' => $mandatorySavings['amount'],
                            'paid_amount' => 0,
                            'balance' => $mandatorySavings['amount'],
                            'status' => 'UNPAID',
                            'due_date' => $dueDate,
                            'months_in_arrears' => 0,
                            'total_arrears' => 0
                        ]);
                        $createdCount++;
                        
                        Log::info('New tracking record created', [
                            'process_id' => $memberProcessId,
                            'tracking_id' => $newRecord->id,
                            'required_amount' => $mandatorySavings['amount'],
                            'balance' => $mandatorySavings['amount']
                        ]);
                    }

                    // Step 5: Generate control numbers
                    Log::info('Step 5: Generating control numbers', [
                        'process_id' => $memberProcessId,
                        'step' => 'control_number_generation'
                    ]);

                    $this->generateControlNumbers($member->client_number);
                    
                    Log::info('Control numbers generated', [
                        'process_id' => $memberProcessId,
                        'control_numbers_count' => count($this->generatedControlNumbers),
                        'control_numbers' => $this->generatedControlNumbers
                    ]);

                    // Step 6: Create service bills
                    Log::info('Step 6: Creating service bills', [
                        'process_id' => $memberProcessId,
                        'step' => 'billing_creation',
                        'control_numbers_count' => count($this->generatedControlNumbers)
                    ]);

                    foreach ($this->generatedControlNumbers as $controlIndex => $control) {
                        $controlProcessId = $memberProcessId . '_control_' . $controlIndex;
                        
                        Log::info('Processing service bill', [
                            'process_id' => $controlProcessId,
                            'service_code' => $control['service_code'],
                            'control_number' => $control['control_number'],
                            'amount' => $control['amount']
                        ]);

                        try {
                            $service = DB::table('services')
                                ->where('code', $control['service_code'])
                                ->first();

                            if ($service) {
                                Log::info('Service found', [
                                    'process_id' => $controlProcessId,
                                    'service_id' => $service->id,
                                    'service_name' => $service->name,
                                    'service_code' => $service->code
                                ]);

                                $billingService = new BillingService();
                                $bill = $billingService->createBill(
                                    $member->client_number,
                                    $service->id,
                                    $service->is_recurring,
                                    $service->payment_mode,
                                    $control['control_number'],
                                    $control['amount']
                                );

                                $billingSuccessCount++;
                                
                                Log::info('Service bill created successfully', [
                                    'process_id' => $controlProcessId,
                                    'bill_id' => $bill->id ?? 'unknown',
                                    'service_id' => $service->id,
                                    'control_number' => $control['control_number'],
                                    'amount' => $control['amount']
                                ]);
                                
                                // Step 7: Send notifications
                                Log::info('Step 7: Sending payment notifications', [
                                    'process_id' => $controlProcessId,
                                    'step' => 'notification_sending'
                                ]);

                                try {
                                    $notificationResult = $this->sendPaymentNotifications(
                                        $member, 
                                        $control['control_number'], 
                                        $control['amount'], 
                                        $dueDate, 
                                        $year, 
                                        $month, 
                                        $mandatorySavings['account_number']
                                    );
                                    
                                    if ($notificationResult['email_sent'] || $notificationResult['sms_sent']) {
                                        $notificationSuccessCount++;
                                        Log::info('Payment notifications sent successfully', [
                                            'process_id' => $controlProcessId,
                                            'email_sent' => $notificationResult['email_sent'],
                                            'sms_sent' => $notificationResult['sms_sent'],
                                            'email_error' => $notificationResult['email_error'],
                                            'sms_error' => $notificationResult['sms_error']
                                        ]);
                                    } else {
                                        $notificationErrorCount++;
                                        Log::warning('Payment notifications failed', [
                                            'process_id' => $controlProcessId,
                                            'email_sent' => $notificationResult['email_sent'],
                                            'sms_sent' => $notificationResult['sms_sent'],
                                            'email_error' => $notificationResult['email_error'],
                                            'sms_error' => $notificationResult['sms_error']
                                        ]);
                                    }
                                } catch (\Exception $notificationError) {
                                    $notificationErrorCount++;
                                    Log::error('Error sending payment notifications', [
                                        'process_id' => $controlProcessId,
                                        'error' => $notificationError->getMessage(),
                                        'trace' => $notificationError->getTraceAsString()
                                    ]);
                                }
                            } else {
                                $billingErrorCount++;
                                Log::error('Service not found', [
                                    'process_id' => $controlProcessId,
                                    'service_code' => $control['service_code'],
                                    'available_services' => DB::table('services')->pluck('code')->toArray()
                                ]);
                            }
                        } catch (\Exception $billingError) {
                            $billingErrorCount++;
                            Log::error('Error creating service bill', [
                                'process_id' => $controlProcessId,
                                'service_code' => $control['service_code'],
                                'control_number' => $control['control_number'],
                                'error' => $billingError->getMessage(),
                                'trace' => $billingError->getTraceAsString()
                            ]);
                        }
                    }

                    $memberProcessingTime = microtime(true) - $memberStartTime;
                    
                    $memberProcessingDetails[] = [
                        'member_number' => $member->client_number,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'processing_time' => round($memberProcessingTime, 4),
                        'status' => 'success',
                        'tracking_created' => !$existingRecord,
                        'tracking_updated' => $existingRecord && $existingRecord->required_amount != $mandatorySavings['amount'],
                        'bills_created' => count($this->generatedControlNumbers),
                        'notifications_sent' => count($this->generatedControlNumbers)
                    ];

                    Log::info('Member processing completed', [
                        'process_id' => $memberProcessId,
                        'processing_time' => round($memberProcessingTime, 4),
                        'status' => 'success'
                    ]);

                } catch (\Exception $memberError) {
                    $errorCount++;
                    $memberProcessingTime = microtime(true) - $memberStartTime;
                    
                    $errorDetails = [
                        'process_id' => $memberProcessId,
                        'member_number' => $member->client_number,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'error' => $memberError->getMessage(),
                        'trace' => $memberError->getTraceAsString(),
                        'processing_time' => round($memberProcessingTime, 4)
                    ];
                    
                    $errors[] = $errorDetails;
                    
                    Log::error('Error processing member', $errorDetails);
                    
                    $memberProcessingDetails[] = [
                        'member_number' => $member->client_number,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'processing_time' => round($memberProcessingTime, 4),
                        'status' => 'error',
                        'error' => $memberError->getMessage()
                    ];
                }
            }

            // Step 8: Commit transaction
            Log::info('Step 8: Committing database transaction', [
                'process_id' => $processId,
                'step' => 'transaction_commit'
            ]);

            DB::commit();

            $totalProcessingTime = microtime(true) - $startTime;
            
            // Step 9: Final summary logging
            Log::info('Mandatory savings tracking records generation completed', [
                'process_id' => $processId,
                'total_processing_time' => round($totalProcessingTime, 4),
                'summary' => [
                    'total_members' => $members->count(),
                    'created_records' => $createdCount,
                    'updated_records' => $updatedCount,
                    'error_count' => $errorCount,
                    'billing_success' => $billingSuccessCount,
                    'billing_errors' => $billingErrorCount,
                    'notification_success' => $notificationSuccessCount,
                    'notification_errors' => $notificationErrorCount
                ],
                'performance_metrics' => [
                    'average_time_per_member' => $members->count() > 0 ? round($totalProcessingTime / $members->count(), 4) : 0,
                    'members_per_second' => $totalProcessingTime > 0 ? round($members->count() / $totalProcessingTime, 2) : 0
                ],
                'member_processing_details' => $memberProcessingDetails,
                'errors' => $errors
            ]);

            return [
                'process_id' => $processId,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'total_members' => $members->count(),
                'error_count' => $errorCount,
                'billing_success' => $billingSuccessCount,
                'billing_errors' => $billingErrorCount,
                'notification_success' => $notificationSuccessCount,
                'notification_errors' => $notificationErrorCount,
                'processing_time' => round($totalProcessingTime, 4),
                'performance_metrics' => [
                    'average_time_per_member' => $members->count() > 0 ? round($totalProcessingTime / $members->count(), 4) : 0,
                    'members_per_second' => $totalProcessingTime > 0 ? round($members->count() / $totalProcessingTime, 2) : 0
                ],
                'member_processing_details' => $memberProcessingDetails,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $totalProcessingTime = microtime(true) - $startTime;
            
            Log::error('Critical error in mandatory savings tracking records generation', [
                'process_id' => $processId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processing_time' => round($totalProcessingTime, 4),
                'year' => $year,
                'month' => $month,
                'context' => [
                    'total_members_processed' => $members->count() ?? 0,
                    'created_count' => $createdCount ?? 0,
                    'updated_count' => $updatedCount ?? 0,
                    'error_count' => $errorCount ?? 0
                ]
            ]);
            
            throw new \Exception('Failed to generate mandatory savings tracking records: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function generateControlNumbers($client_number)
    {
        $billingService = new BillingService();
        
        // Get the SAV (Savings Deposit) service
        $service = DB::table('services')
            ->where('code', 'SAV')
            ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
            ->first();

        $this->generatedControlNumbers = [];

        if ($service) {
            // Generate control number for SAV service
            $controlNumber = $billingService->generateControlNumber(
                $client_number,
                $service->id,
                $service->is_recurring,
                $service->payment_mode
            );

            $this->generatedControlNumbers[] = [
                'service_code' => $service->code,
                'control_number' => $controlNumber,
                'amount' => $this->getMandatorySavingsAmount()['amount'] // Use mandatory savings amount instead of service lower_limit
            ];
        }
    }

    /**
     * Update tracking records based on actual payments from general ledger.
     */
    public function updateTrackingFromPayments($year, $month)
    {
        try {
            DB::beginTransaction();

            $mandatorySavings = $this->getMandatorySavingsAmount();
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            // Get all payments for mandatory savings account in the specified month
            $payments = general_ledger::where('credit', '>', 0)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('product_number', '2000')
                ->get();

            $updatedCount = 0;

            foreach ($payments as $payment) {
                // Find the tracking record for this payment
                $clientNumber = DB::table('accounts')->where('account_number', $payment->record_on_account_number)->first()->client_number;
                $trackingRecord = MandatorySavingsTracking::where('client_number', $clientNumber)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if ($trackingRecord) {
                    $trackingRecord->recordPayment($payment->credit, $payment->created_at);
                    $updatedCount++;
                }
            }

            DB::commit();

            return [
                'updated_records' => $updatedCount,
                'total_payments' => $payments->count()
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating tracking from payments: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate notifications for upcoming payments.
     */
    public function generateNotifications($year, $month)
    {
        try {
            // Check if settings exist
            if (!$this->getSettings()) {
                return ['message' => 'Mandatory savings settings not configured. Please configure settings first.'];
            }
            
            if (!$this->getSettings()->notificationsEnabled()) {
                return ['message' => 'Notifications are disabled'];
            }

            DB::beginTransaction();

            $unpaidRecords = MandatorySavingsTracking::where('year', $year)
                ->where('month', $month)
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->with('client')
                ->get();

            $notificationsCreated = 0;

            foreach ($unpaidRecords as $record) {
                // Generate first reminder
                $this->createNotification($record, 'FIRST_REMINDER', $year, $month);
                
                // Generate second reminder
                $this->createNotification($record, 'SECOND_REMINDER', $year, $month);
                
                // Generate final reminder
                $this->createNotification($record, 'FINAL_REMINDER', $year, $month);

                $notificationsCreated += 3;
            }

            DB::commit();

            return [
                'notifications_created' => $notificationsCreated,
                'members_notified' => $unpaidRecords->count()
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating notifications: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a notification for a tracking record.
     */
    protected function createNotification($trackingRecord, $type, $year, $month)
    {
        // Check if settings exist
        if (!$this->getSettings()) {
            Log::warning('Cannot create notification: Mandatory savings settings not configured');
            return;
        }
        
        $scheduledDate = match($type) {
            'FIRST_REMINDER' => $this->getSettings()->getFirstReminderDate($year, $month),
            'SECOND_REMINDER' => $this->getSettings()->getSecondReminderDate($year, $month),
            'FINAL_REMINDER' => $this->getSettings()->getFinalReminderDate($year, $month),
            default => now()
        };

        // Check if notification already exists
        $existingNotification = MandatorySavingsNotification::where('client_number', $trackingRecord->client_number)
            ->where('year', $year)
            ->where('month', $month)
            ->where('notification_type', $type)
            ->first();

        if (!$existingNotification) {
            $message = $this->generateNotificationMessage($trackingRecord, $type);
            
            MandatorySavingsNotification::create([
                'client_number' => $trackingRecord->client_number,
                'account_number' => $trackingRecord->account_number,
                'year' => $year,
                'month' => $month,
                'notification_type' => $type,
                'notification_method' => 'SYSTEM',
                'message' => $message,
                'status' => 'PENDING',
                'scheduled_at' => $scheduledDate
            ]);
        }
    }

    /**
     * Generate notification message.
     */
    protected function generateNotificationMessage($trackingRecord, $type)
    {
        $client = $trackingRecord->client;
        $amount = number_format($trackingRecord->balance, 2);
        $period = $trackingRecord->period;
        $dueDate = $trackingRecord->due_date->format('d/m/Y');

        $urgency = match($type) {
            'FIRST_REMINDER' => 'friendly reminder',
            'SECOND_REMINDER' => 'important reminder',
            'FINAL_REMINDER' => 'urgent reminder',
            default => 'reminder'
        };

        return "Dear {$client->first_name} {$client->last_name}, this is a {$urgency} that your mandatory savings payment of TZS {$amount} for {$period} is due on {$dueDate}. Please make your payment to avoid penalties.";
    }

    /**
     * Calculate arrears for all members.
     */
    public function calculateArrears()
    {
        try {
            $members = ClientsModel::where('status', 'ACTIVE')->get();
            $arrearsData = [];

            foreach ($members as $member) {
                $arrears = MandatorySavingsTracking::calculateArrears($member->client_number);
                
                if ($arrears['total_arrears'] > 0) {
                    $arrearsData[] = [
                        'client_number' => $member->client_number,
                        'client_name' => $member->first_name . ' ' . $member->last_name,
                        'total_arrears' => $arrears['total_arrears'],
                        'months_in_arrears' => $arrears['months_in_arrears'],
                        'unpaid_records' => $arrears['unpaid_records']
                    ];
                }
            }

            return $arrearsData;
        } catch (\Exception $e) {
            Log::error('Error calculating arrears: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get summary statistics for mandatory savings.
     */
    public function getSummaryStatistics($year = null, $month = null)
    {
        try {
            $query = MandatorySavingsTracking::query();

            if ($year && $month) {
                $query->where('year', $year)->where('month', $month);
            }

            $totalRecords = $query->count();
            $paidRecords = $query->where('status', 'PAID')->count();
            $unpaidRecords = $query->whereIn('status', ['UNPAID', 'PARTIAL'])->count();
            $overdueRecords = $query->where('status', 'OVERDUE')->count();

            $totalRequired = $query->sum('required_amount');
            $totalPaid = $query->sum('paid_amount');
            $totalOutstanding = $query->sum('balance');

            return [
                'total_records' => $totalRecords,
                'paid_records' => $paidRecords,
                'unpaid_records' => $unpaidRecords,
                'overdue_records' => $overdueRecords,
                'total_required' => $totalRequired,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'compliance_rate' => $totalRecords > 0 ? ($paidRecords / $totalRecords) * 100 : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error getting summary statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process overdue records and update status.
     */
    public function processOverdueRecords()
    {
        try {
            $overdueRecords = MandatorySavingsTracking::where('due_date', '<', now())
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->get();

            $updatedCount = 0;

            foreach ($overdueRecords as $record) {
                $record->updateStatus();
                if ($record->status === 'OVERDUE') {
                    $updatedCount++;
                }
            }

            return [
                'updated_records' => $updatedCount,
                'total_overdue' => $overdueRecords->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error processing overdue records: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function sendPaymentNotifications($member, $controlNumber, $amount, $dueDate, $year, $month, $accountNumber)
    {
        try {
            $notificationService = new \App\Services\NotificationServicex();
            
            $result = $notificationService->sendMandatorySavingsNotification(
                $member, 
                $controlNumber, 
                $amount, 
                $dueDate, 
                $year, 
                $month, 
                $accountNumber
            );
            
            Log::info('Payment notifications sent via central service', [
                'member' => $member->client_number,
                'control_number' => $controlNumber,
                'email_sent' => $result['email_sent'],
                'sms_sent' => $result['sms_sent'],
                'email_error' => $result['email_error'],
                'sms_error' => $result['sms_error']
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error sending payment notifications: ' . $e->getMessage(), [
                'member' => $member->client_number,
                'control_number' => $controlNumber
            ]);
            throw $e;
        }
    }
} 