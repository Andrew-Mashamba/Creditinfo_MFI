<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MandatorySavingsService;
use App\Models\MandatorySavingsSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessMandatorySavings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mandatory-savings:process {--month= : Specific month (1-12)} {--year= : Specific year} {--action=all : Action to perform (generate, update, notify, overdue, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process mandatory savings tracking, payments, and notifications';

    protected $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // Only initialize service if we're not in a migration context
        try {
            $this->service = new MandatorySavingsService();
        } catch (\Exception $e) {
            // Service will be initialized when needed
            $this->service = null;
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Initialize service if not already done
            if (!$this->service) {
                $this->service = new MandatorySavingsService();
            }
            
            $month = $this->option('month') ?: Carbon::now()->month;
            $year = $this->option('year') ?: Carbon::now()->year;
            $action = $this->option('action');

            $this->info("Processing mandatory savings for {$month}/{$year} - Action: {$action}");

            // Validate inputs
            if ($month < 1 || $month > 12) {
                $this->error('Invalid month. Must be between 1 and 12.');
                return 1;
            }

            if ($year < 2000 || $year > 2100) {
                $this->error('Invalid year. Must be between 2000 and 2100.');
                return 1;
            }

            // Check if settings exist
            $settings = MandatorySavingsSettings::forInstitution('1');
            if (!$settings) {
                $this->error('Mandatory savings settings not found. Please configure settings first.');
                return 1;
            }

            $this->info("Settings found: Monthly amount TZS " . number_format($settings->monthly_amount, 2));

            // Perform requested actions
            switch ($action) {
                case 'generate':
                    $this->generateTrackingRecords($year, $month);
                    break;
                case 'update':
                    $this->updateFromPayments($year, $month);
                    break;
                case 'notify':
                    $this->generateNotifications($year, $month);
                    break;
                case 'overdue':
                    $this->processOverdueRecords();
                    break;
                case 'all':
                default:
                    $this->generateTrackingRecords($year, $month);
                    $this->updateFromPayments($year, $month);
                    $this->processOverdueRecords();
                    $this->generateNotifications($year, $month);
                    break;
            }

            $this->info('Mandatory savings processing completed successfully.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error processing mandatory savings: ' . $e->getMessage());
            Log::error('Mandatory savings processing error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate tracking records for the specified period.
     */
    protected function generateTrackingRecords($year, $month)
    {
        $this->info('Generating tracking records...');
        
        $result = $this->service->generateTrackingRecords($year, $month);
        
        $this->info("✓ Generated {$result['created']} new records");
        $this->info("✓ Updated {$result['updated']} existing records");
        $this->info("✓ Processed {$result['total_members']} total members");
    }

    /**
     * Update tracking records from payments.
     */
    protected function updateFromPayments($year, $month)
    {
        $this->info('Updating tracking records from payments...');
        
        $result = $this->service->updateTrackingFromPayments($year, $month);
        
        $this->info("✓ Updated {$result['updated_records']} records from {$result['total_payments']} payments");
    }

    /**
     * Generate notifications for the specified period.
     */
    protected function generateNotifications($year, $month)
    {
        $this->info('Generating notifications...');
        
        $result = $this->service->generateNotifications($year, $month);
        
        if (isset($result['message'])) {
            $this->warn($result['message']);
        } else {
            $this->info("✓ Generated {$result['notifications_created']} notifications");
            $this->info("✓ Notified {$result['members_notified']} members");
        }
    }

    /**
     * Process overdue records.
     */
    protected function processOverdueRecords()
    {
        $this->info('Processing overdue records...');
        
        $result = $this->service->processOverdueRecords();
        
        $this->info("✓ Updated {$result['updated_records']} overdue records");
        $this->info("✓ Found {$result['total_overdue']} total overdue records");
    }
} 