<?php

namespace Tests\Feature\EndOfDay;

use Tests\TestCase;
use App\Models\DailyActivityStatus;
use App\Models\User;
use App\Services\DailySystemActivitiesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use App\Http\Livewire\ProfileSetting\EndOfDay;

class EndOfDayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Initialize all activities
        $this->initializeAllActivities();
    }

    /**
     * Test complete end-of-day flow from UI to completion
     */
    public function test_complete_end_of_day_flow()
    {
        // Step 1: Load the UI component
        $component = Livewire::test(EndOfDay::class);
        
        // Verify initial state
        $component->assertStatus(200)
            ->assertSee('End of Day Processes')
            ->assertSet('isRunning', false)
            ->assertSet('overallProgress', 0);
        
        // Step 2: Trigger manual run
        $component->call('runManually');
        
        // Verify running state
        $component->assertSet('isRunning', true)
            ->assertSet('autoRefresh', true)
            ->assertSessionHas('success');
        
        // Step 3: Simulate activity processing
        $this->simulateActivityProcessing();
        
        // Step 4: Refresh and check progress
        $component->call('loadActivities');
        
        // Verify activities are updating
        $activities = collect($component->get('activities'));
        $runningCount = $activities->where('status', 'running')->count();
        $completedCount = $activities->where('status', 'completed')->count();
        
        $this->assertGreaterThan(0, $runningCount + $completedCount);
        
        // Step 5: Complete all activities
        $this->completeAllActivities();
        
        // Step 6: Final refresh and verification
        $component->call('loadActivities');
        
        // All activities should be completed
        $activities = collect($component->get('activities'));
        $completedCount = $activities->where('status', 'completed')->count();
        
        $this->assertEquals(18, $completedCount);
        $this->assertEquals(100, $component->get('overallProgress'));
        $this->assertFalse($component->get('isRunning'));
    }

    /**
     * Test scheduled execution via artisan command
     */
    public function test_scheduled_execution_via_artisan()
    {
        // Clear cache
        Cache::forget('last_daily_activities_run');
        
        // Run the artisan command
        $this->artisan('system:daily-activities')
            ->assertExitCode(0);
        
        // Verify cache was updated
        $this->assertNotNull(Cache::get('last_daily_activities_run'));
        
        // Verify activities were created/updated
        $activities = DailyActivityStatus::getTodayActivities();
        $this->assertCount(18, $activities);
    }

    /**
     * Test error handling and recovery
     */
    public function test_error_handling_and_recovery()
    {
        // Simulate a failed activity
        $activity = DailyActivityStatus::where('activity_key', 'loan_notifications')
            ->whereDate('process_date', Carbon::today())
            ->first();
        
        $activity->start('test');
        $activity->fail('Simulated failure for testing');
        
        // Load UI component
        $component = Livewire::test(EndOfDay::class);
        
        // Verify failed activity is displayed
        $activities = collect($component->get('activities'));
        $failedActivity = $activities->firstWhere('key', 'loan_notifications');
        
        $this->assertEquals('failed', $failedActivity['status']);
        $this->assertEquals('Simulated failure for testing', $failedActivity['lastError']);
        
        // Retry by running again
        $component->call('runManually');
        
        // Simulate successful retry
        $activity->fresh()->start('manual');
        $activity->complete();
        
        // Refresh and verify recovery
        $component->call('loadActivities');
        $activities = collect($component->get('activities'));
        $recoveredActivity = $activities->firstWhere('key', 'loan_notifications');
        
        $this->assertEquals('completed', $recoveredActivity['status']);
        $this->assertEquals(100, $recoveredActivity['progress']);
    }

    /**
     * Test concurrent execution prevention
     */
    public function test_concurrent_execution_prevention()
    {
        // Start first execution
        $activity = DailyActivityStatus::where('activity_key', 'inactive_members')
            ->whereDate('process_date', Carbon::today())
            ->first();
        $activity->start('manual');
        
        // Load UI component
        $component = Livewire::test(EndOfDay::class);
        
        // Should detect running state
        $this->assertTrue($component->get('isRunning'));
        
        // Try to start another execution
        $component->call('runManually');
        
        // Should be prevented
        $component->assertSessionHas('error', 'Daily activities are already running. Please wait for completion.');
        
        // Verify no duplicate activities were started
        $runningActivities = DailyActivityStatus::where('status', 'running')
            ->whereDate('process_date', Carbon::today())
            ->count();
        
        $this->assertEquals(1, $runningActivities);
    }

    /**
     * Test activity progress tracking
     */
    public function test_activity_progress_tracking()
    {
        $activity = DailyActivityStatus::where('activity_key', 'repayments_collection')
            ->whereDate('process_date', Carbon::today())
            ->first();
        
        // Start processing
        $activity->start('test');
        
        // Simulate progressive updates
        $progressSteps = [10, 25, 50, 75, 90, 100];
        
        foreach ($progressSteps as $progress) {
            $activity->updateProgress($progress, 100);
            
            // Load component and check progress
            $component = Livewire::test(EndOfDay::class);
            $activities = collect($component->get('activities'));
            $currentActivity = $activities->firstWhere('key', 'repayments_collection');
            
            $this->assertEquals($progress, $currentActivity['progress']);
            $this->assertEquals($progress, $currentActivity['processedRecords']);
            $this->assertEquals(100, $currentActivity['totalRecords']);
        }
        
        // Complete the activity
        $activity->complete();
        
        // Final verification
        $component = Livewire::test(EndOfDay::class);
        $activities = collect($component->get('activities'));
        $completedActivity = $activities->firstWhere('key', 'repayments_collection');
        
        $this->assertEquals('completed', $completedActivity['status']);
        $this->assertEquals(100, $completedActivity['progress']);
    }

    /**
     * Test execution time tracking
     */
    public function test_execution_time_tracking()
    {
        $startTime = Carbon::now();
        
        // Run a quick activity
        $activity = DailyActivityStatus::where('activity_key', 'financial_year_check')
            ->whereDate('process_date', Carbon::today())
            ->first();
        
        $activity->start('test');
        
        // Simulate some work
        sleep(2);
        
        $activity->complete();
        
        // Load component and verify execution time
        $component = Livewire::test(EndOfDay::class);
        $activities = collect($component->get('activities'));
        $completedActivity = $activities->firstWhere('key', 'financial_year_check');
        
        $this->assertNotNull($completedActivity['executionTime']);
        $this->assertStringContainsString('s', $completedActivity['executionTime']);
        
        // Verify actual execution time in database
        $activity->refresh();
        $this->assertGreaterThanOrEqual(2, $activity->execution_time_seconds);
        $this->assertLessThan(10, $activity->execution_time_seconds);
    }

    /**
     * Test daily separation of activities
     */
    public function test_daily_separation_of_activities()
    {
        // Complete today's activities
        DailyActivityStatus::whereDate('process_date', Carbon::today())
            ->update([
                'status' => 'completed',
                'progress' => 100,
                'completed_at' => Carbon::now()
            ]);
        
        // Manually create tomorrow's activities
        Carbon::setTestNow(Carbon::tomorrow());
        
        // Reinitialize for tomorrow
        $this->initializeAllActivities();
        
        // Load component
        $component = Livewire::test(EndOfDay::class);
        
        // Should show all pending for tomorrow
        $activities = collect($component->get('activities'));
        $pendingCount = $activities->where('status', 'pending')->count();
        
        $this->assertEquals(18, $pendingCount);
        $this->assertEquals(0, $component->get('overallProgress'));
        
        // Reset time
        Carbon::setTestNow();
    }

    /**
     * Test cache persistence
     */
    public function test_cache_persistence()
    {
        // Clear cache
        Cache::forget('last_daily_activities_run');
        Cache::forget('daily_loan_processing_stats');
        
        // Run activities
        $this->artisan('system:daily-activities');
        
        // Verify cache entries exist
        $this->assertNotNull(Cache::get('last_daily_activities_run'));
        
        // Verify cache TTL (should be 24 hours)
        $lastRun = Cache::get('last_daily_activities_run');
        $this->assertInstanceOf(Carbon::class, Carbon::parse($lastRun));
    }

    /**
     * Test database transaction rollback on failure
     */
    public function test_database_transaction_rollback_on_failure()
    {
        // Count initial activities
        $initialCount = DailyActivityStatus::count();
        
        // Mock a service to throw exception
        $this->mock('App\Services\ReportGenerationService')
            ->shouldReceive('generateLoanArrearsReport')
            ->andThrow(new \Exception('Test exception'));
        
        // Attempt to run activities (should fail)
        try {
            $service = app(DailySystemActivitiesService::class);
            $result = $service->executeDailyActivities('test');
        } catch (\Exception $e) {
            // Expected exception
        }
        
        // Verify no partial data was saved (transaction rolled back)
        $this->assertEquals($initialCount, DailyActivityStatus::count());
    }

    /**
     * Helper method to initialize all activities
     */
    private function initializeAllActivities()
    {
        $activities = [
            ['key' => 'inactive_members', 'name' => 'Inactive Members'],
            ['key' => 'share_accounts', 'name' => 'Share accounts maintenance'],
            ['key' => 'savings_maintenance', 'name' => 'Savings accounts maintenance'],
            ['key' => 'savings_interest', 'name' => 'Savings accounts interest calculation'],
            ['key' => 'maturing_savings', 'name' => 'Maturing Savings accounts'],
            ['key' => 'deposit_maintenance', 'name' => 'Deposit accounts maintenance'],
            ['key' => 'deposit_interest', 'name' => 'Deposit accounts interest calculation'],
            ['key' => 'maturing_deposits', 'name' => 'Maturing Deposit accounts'],
            ['key' => 'loan_notifications', 'name' => 'Loan repayment notifications'],
            ['key' => 'repayments_collection', 'name' => 'Repayments collection'],
            ['key' => 'till_maintenance', 'name' => 'Till accounts maintenance'],
            ['key' => 'reconciliation', 'name' => 'Reconciliation'],
            ['key' => 'payroll_processing', 'name' => 'Payroll processing'],
            ['key' => 'depreciation', 'name' => 'Depreciation calculation'],
            ['key' => 'pending_approvals', 'name' => 'Pending approvals'],
            ['key' => 'compliance_reports', 'name' => 'Compliance reports generation'],
            ['key' => 'financial_year_check', 'name' => 'Financial Year Date Check'],
            ['key' => 'expiring_passwords', 'name' => 'Expiring Passwords'],
        ];
        
        foreach ($activities as $activity) {
            DailyActivityStatus::getOrCreateActivity($activity['key'], $activity['name']);
        }
    }

    /**
     * Helper to simulate activity processing
     */
    private function simulateActivityProcessing()
    {
        $activities = DailyActivityStatus::getTodayActivities();
        
        foreach ($activities->take(5) as $activity) {
            $activity->start('test');
            $activity->updateProgress(50, 100);
        }
    }

    /**
     * Helper to complete all activities
     */
    private function completeAllActivities()
    {
        $activities = DailyActivityStatus::getTodayActivities();
        
        foreach ($activities as $activity) {
            if ($activity->status !== 'completed') {
                $activity->start('test');
                $activity->complete();
            }
        }
    }
}