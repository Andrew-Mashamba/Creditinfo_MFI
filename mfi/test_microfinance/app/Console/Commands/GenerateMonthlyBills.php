<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sacco;
use App\Models\Member;
use App\Models\Service;
use App\Models\BillingCycle;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:generate-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly bills for all SACCO members';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting monthly bill generation...');

        try {
            DB::beginTransaction();

            // Create new billing cycle
            $billingCycle = BillingCycle::create([
                'month_year' => Carbon::now()->format('Y-m'),
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->endOfMonth(),
                'status' => 'Open'
            ]);

            // Process each SACCO
            $saccos = Sacco::with('members')->get();
            foreach ($saccos as $sacco) {
                $this->info("Processing SACCO: {$sacco->name}");

                // Process each member
                foreach ($sacco->members as $member) {
                    $this->info("Processing Member: {$member->name}");

                    // Get mandatory services
                    $mandatoryServices = Service::where('is_mandatory', true)->get();

                    foreach ($mandatoryServices as $service) {
                        $this->generateBill($sacco, $member, $service, $billingCycle);
                    }
                }
            }

            DB::commit();
            $this->info('Monthly bill generation completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Monthly bill generation failed: ' . $e->getMessage());
            $this->error('Failed to generate monthly bills: ' . $e->getMessage());
        }
    }

    protected function generateBill($sacco, $member, $service, $billingCycle)
    {
        try {
            // Generate control number
            $controlNumber = '1' .
                str_pad($sacco->code, 4, '0', STR_PAD_LEFT) .
                str_pad($member->member_number, 5, '0', STR_PAD_LEFT) .
                $service->code .
                '1' . // Recurring monthly
                $service->default_mode;

            // Create bill
            Bill::create([
                'sacco_id' => $sacco->id,
                'member_id' => $member->id,
                'service_id' => $service->id,
                'billing_cycle_id' => $billingCycle->id,
                'amount_due' => $service->lower_limit,
                'amount_paid' => 0,
                'control_number' => $controlNumber,
                'is_mandatory' => true,
                'is_recurring' => 1,
                'payment_mode' => $service->default_mode,
                'due_date' => Carbon::now()->endOfMonth(),
                'status' => 'Pending',
                'created_by' => 1 // System user
            ]);

            $this->info("Generated bill for {$member->name} - {$service->name}");

        } catch (\Exception $e) {
            Log::error("Failed to generate bill for member {$member->id}, service {$service->id}: " . $e->getMessage());
            $this->error("Failed to generate bill for {$member->name} - {$service->name}");
        }
    }
}
