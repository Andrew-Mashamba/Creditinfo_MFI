<?php

namespace Tests\Feature\EndOfDay;

use Tests\TestCase;
use App\Services\DailySystemActivitiesService;
use App\Models\DailyActivityStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

class DailySystemActivitiesTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock dependencies
        $interestService = Mockery::mock('App\Services\InterestCalculationService');
        $reportService = Mockery::mock('App\Services\ReportGenerationService');
        $securityService = Mockery::mock('App\Services\SecurityService');
        $backupService = Mockery::mock('App\Services\BackupService');
        $mandatorySavingsService = Mockery::mock('App\Services\MandatorySavingsService');
        
        // Set up default mock expectations
        $reportService->shouldReceive('generateLoanArrearsReport')->andReturn(true);
        $reportService->shouldReceive('generateDepositInterestReport')->andReturn(true);
        $reportService->shouldReceive('generateShareMovementReport')->andReturn(true);
        $reportService->shouldReceive('generateDailyTrialBalance')->andReturn(true);
        $reportService->shouldReceive('generateMemberStatements')->andReturn(true);
        $reportService->shouldReceive('generateRegulatoryReports')->andReturn(true);
        
        $securityService->shouldReceive('auditUserActivities')->andReturn(true);
        $securityService->shouldReceive('updateAccessLogs')->andReturn(true);
        $securityService->shouldReceive('checkSuspiciousActivities')->andReturn(true);
        $securityService->shouldReceive('rotateSecurityKeys')->andReturn(true);
        $securityService->shouldReceive('updateSessionRecords')->andReturn(true);
        
        $backupService->shouldReceive('createDatabaseBackup')->andReturn(true);
        
        $this->service = new DailySystemActivitiesService(
            $interestService,
            $reportService,
            $securityService,
            $backupService,
            $mandatorySavingsService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that daily activities can be executed successfully
     */
    public function test_execute_daily_activities_successfully()
    {
        // Initialize activities
        $this->initializeActivities();
        
        // Execute daily activities
        $result = $this->service->executeDailyActivities('test');
        
        // Assert success
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('date', $result);
        
        // Check cache was updated
        $this->assertNotNull(Cache::get('last_daily_activities_run'));
    }

    /**
     * Test that activities are tracked properly
     */
    public function test_activities_are_tracked_in_database()
    {
        // Initialize activities
        $this->initializeActivities();
        
        // Execute daily activities
        $this->service->executeDailyActivities('manual');
        
        // Check that activities were tracked
        $activities = DailyActivityStatus::getTodayActivities();
        $this->assertGreaterThan(0, $activities->count());
        
        // Check for specific activities
        $loanActivity = DailyActivityStatus::where('activity_key', 'repayments_collection')
            ->whereDate('process_date', Carbon::today())
            ->first();
            
        $this->assertNotNull($loanActivity);
        $this->assertEquals('manual', $loanActivity->triggered_by);
    }

    /**
     * Test that failed activities are properly marked
     */
    public function test_failed_activities_are_marked_correctly()
    {
        // Create an activity
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_failure',
            'activity_name' => 'Test Failure Activity',
            'status' => 'pending',
            'progress' => 0,
            'process_date' => Carbon::today()
        ]);
        
        // Start and fail the activity
        $activity->start('test');
        $activity->fail('Test error message');
        
        // Verify status
        $this->assertEquals('failed', $activity->status);
        $this->assertEquals('Test error message', $activity->last_error);
        $this->assertNotNull($activity->completed_at);
    }

    /**
     * Test that multiple activities can run in sequence
     */
    public function test_multiple_activities_run_in_sequence()
    {
        $activities = [
            'inactive_members' => 'Inactive Members',
            'share_accounts' => 'Share accounts maintenance',
            'savings_maintenance' => 'Savings accounts maintenance'
        ];
        
        foreach ($activities as $key => $name) {
            $activity = DailyActivityStatus::getOrCreateActivity($key, $name);
            $activity->start('test');
            
            // Simulate some progress
            $activity->updateProgress(50, 100);
            $activity->updateProgress(100, 100);
            
            $activity->complete();
            
            $this->assertEquals('completed', $activity->fresh()->status);
            $this->assertEquals(100, $activity->fresh()->progress);
        }
    }

    /**
     * Test cache is updated after execution
     */
    public function test_cache_is_updated_after_execution()
    {
        // Clear cache first
        Cache::forget('last_daily_activities_run');
        
        // Execute activities
        $this->service->executeDailyActivities('scheduled');
        
        // Check cache
        $lastRun = Cache::get('last_daily_activities_run');
        $this->assertNotNull($lastRun);
        
        // Verify it's recent (within last minute)
        $this->assertTrue(Carbon::parse($lastRun)->diffInMinutes(Carbon::now()) < 1);
    }

    /**
     * Test progress calculation
     */
    public function test_progress_calculation()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_progress',
            'activity_name' => 'Test Progress',
            'status' => 'running',
            'progress' => 0,
            'process_date' => Carbon::today()
        ]);
        
        // Test various progress scenarios
        $activity->updateProgress(0, 100);
        $this->assertEquals(0, $activity->progress);
        
        $activity->updateProgress(25, 100);
        $this->assertEquals(25, $activity->progress);
        
        $activity->updateProgress(50, 100);
        $this->assertEquals(50, $activity->progress);
        
        $activity->updateProgress(100, 100);
        $this->assertEquals(100, $activity->progress);
        
        // Test with zero total (edge case)
        $activity->updateProgress(0, 0);
        $this->assertEquals(0, $activity->progress);
    }

    /**
     * Test execution time tracking
     */
    public function test_execution_time_is_tracked()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_timing',
            'activity_name' => 'Test Timing',
            'status' => 'pending',
            'progress' => 0,
            'process_date' => Carbon::today()
        ]);
        
        $activity->start('test');
        
        // Simulate some work
        sleep(2);
        
        $activity->complete();
        
        $this->assertNotNull($activity->execution_time_seconds);
        $this->assertGreaterThanOrEqual(2, $activity->execution_time_seconds);
    }

    /**
     * Test that activities for different dates are separate
     */
    public function test_activities_are_separated_by_date()
    {
        // Create activity for today
        $todayActivity = DailyActivityStatus::getOrCreateActivity('test_activity', 'Test Activity');
        $todayActivity->update(['progress' => 50]);
        
        // Manually create activity for tomorrow
        $tomorrowActivity = DailyActivityStatus::create([
            'activity_key' => 'test_activity',
            'activity_name' => 'Test Activity',
            'status' => 'pending',
            'progress' => 0,
            'process_date' => Carbon::tomorrow()
        ]);
        
        // Verify they are different records
        $this->assertNotEquals($todayActivity->id, $tomorrowActivity->id);
        $this->assertEquals(50, $todayActivity->progress);
        $this->assertEquals(0, $tomorrowActivity->progress);
        
        // Verify getTodayActivities only returns today's
        $todayActivities = DailyActivityStatus::getTodayActivities();
        $this->assertTrue($todayActivities->contains('id', $todayActivity->id));
        $this->assertFalse($todayActivities->contains('id', $tomorrowActivity->id));
    }

    /**
     * Helper method to initialize activities
     */
    private function initializeActivities()
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
}