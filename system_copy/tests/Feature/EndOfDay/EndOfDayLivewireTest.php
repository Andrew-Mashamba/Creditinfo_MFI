<?php

namespace Tests\Feature\EndOfDay;

use Tests\TestCase;
use Livewire\Livewire;
use App\Http\Livewire\ProfileSetting\EndOfDay;
use App\Models\DailyActivityStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class EndOfDayLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Initialize activities
        $this->initializeActivities();
    }

    /**
     * Test that component renders properly
     */
    public function test_component_renders_successfully()
    {
        Livewire::test(EndOfDay::class)
            ->assertStatus(200)
            ->assertSee('End of Day Processes')
            ->assertSee('Last Run')
            ->assertSee('Next Run')
            ->assertSee('Overall Progress')
            ->assertSee('RUN NOW');
    }

    /**
     * Test that all 18 activities are displayed
     */
    public function test_all_activities_are_displayed()
    {
        $component = Livewire::test(EndOfDay::class);
        
        $expectedActivities = [
            'Inactive Members',
            'Share accounts maintenance',
            'Savings accounts maintenance',
            'Savings accounts interest calculation',
            'Maturing Savings accounts',
            'Deposit accounts maintenance',
            'Deposit accounts interest calculation',
            'Maturing Deposit accounts',
            'Loan repayment notifications',
            'Repayments collection',
            'Till accounts maintenance',
            'Reconciliation',
            'Payroll processing',
            'Depreciation calculation',
            'Pending approvals',
            'Compliance reports generation',
            'Financial Year Date Check',
            'Expiring Passwords'
        ];
        
        foreach ($expectedActivities as $activity) {
            $component->assertSee($activity);
        }
    }

    /**
     * Test loading activities from database
     */
    public function test_loads_activities_from_database()
    {
        // Create some test activities with different statuses
        DailyActivityStatus::where('activity_key', 'inactive_members')
            ->whereDate('process_date', Carbon::today())
            ->update([
                'status' => 'completed',
                'progress' => 100,
                'processed_records' => 150,
                'total_records' => 150
            ]);
            
        DailyActivityStatus::where('activity_key', 'share_accounts')
            ->whereDate('process_date', Carbon::today())
            ->update([
                'status' => 'running',
                'progress' => 45,
                'processed_records' => 45,
                'total_records' => 100
            ]);
            
        DailyActivityStatus::where('activity_key', 'savings_maintenance')
            ->whereDate('process_date', Carbon::today())
            ->update([
                'status' => 'failed',
                'progress' => 30,
                'last_error' => 'Database connection timeout'
            ]);
        
        $component = Livewire::test(EndOfDay::class);
        
        // Check that activities are loaded with correct status
        $this->assertCount(18, $component->get('activities'));
        
        // Find specific activities and check their properties
        $activities = collect($component->get('activities'));
        
        $inactiveMembers = $activities->firstWhere('key', 'inactive_members');
        $this->assertEquals('completed', $inactiveMembers['status']);
        $this->assertEquals(100, $inactiveMembers['progress']);
        
        $shareAccounts = $activities->firstWhere('key', 'share_accounts');
        $this->assertEquals('running', $shareAccounts['status']);
        $this->assertEquals(45, $shareAccounts['progress']);
        
        $savingsMaintenance = $activities->firstWhere('key', 'savings_maintenance');
        $this->assertEquals('failed', $savingsMaintenance['status']);
        $this->assertEquals('Database connection timeout', $savingsMaintenance['lastError']);
    }

    /**
     * Test overall progress calculation
     */
    public function test_calculates_overall_progress_correctly()
    {
        // Set up activities with different completion states
        DailyActivityStatus::whereDate('process_date', Carbon::today())
            ->take(9) // Complete half of 18 activities
            ->update(['status' => 'completed', 'progress' => 100]);
        
        $component = Livewire::test(EndOfDay::class);
        
        // Should be 50% (9 out of 18)
        $this->assertEquals(50, $component->get('overallProgress'));
    }

    /**
     * Test manual trigger functionality
     */
    public function test_manual_trigger_dispatches_job()
    {
        Queue::fake();
        
        Livewire::test(EndOfDay::class)
            ->call('runManually')
            ->assertHasNoErrors()
            ->assertSet('isRunning', true)
            ->assertSet('autoRefresh', true);
        
        Queue::assertPushed(function ($job) {
            return true; // Check that a job was dispatched
        });
    }

    /**
     * Test that manual trigger is disabled when already running
     */
    public function test_manual_trigger_disabled_when_running()
    {
        // Set one activity as running
        DailyActivityStatus::where('activity_key', 'inactive_members')
            ->whereDate('process_date', Carbon::today())
            ->update(['status' => 'running']);
        
        $component = Livewire::test(EndOfDay::class);
        
        // Component should detect running status
        $this->assertTrue($component->get('isRunning'));
        
        // Try to run manually
        $component->call('runManually');
        
        // Should show error message
        $component->assertSessionHas('error', 'Daily activities are already running. Please wait for completion.');
    }

    /**
     * Test auto-refresh toggle
     */
    public function test_auto_refresh_toggle()
    {
        $component = Livewire::test(EndOfDay::class);
        
        // Default should be true
        $this->assertTrue($component->get('autoRefresh'));
        
        // Toggle off
        $component->call('toggleAutoRefresh');
        $this->assertFalse($component->get('autoRefresh'));
        
        // Toggle back on
        $component->call('toggleAutoRefresh');
        $this->assertTrue($component->get('autoRefresh'));
    }

    /**
     * Test next run time calculation
     */
    public function test_next_run_time_calculation()
    {
        $component = Livewire::test(EndOfDay::class);
        
        $nextRunTime = $component->get('nextRunTime');
        $this->assertNotNull($nextRunTime);
        
        // Parse the time and check it's at 00:05
        $nextRun = Carbon::parse($nextRunTime);
        $this->assertEquals(0, $nextRun->hour);
        $this->assertEquals(5, $nextRun->minute);
        
        // Should be either today or tomorrow depending on current time
        $now = Carbon::now();
        if ($now->hour > 0 || ($now->hour == 0 && $now->minute >= 5)) {
            // Should be tomorrow
            $this->assertEquals(Carbon::tomorrow()->day, $nextRun->day);
        } else {
            // Should be today
            $this->assertEquals(Carbon::today()->day, $nextRun->day);
        }
    }

    /**
     * Test last run date display
     */
    public function test_last_run_date_display()
    {
        // Set cache for last run
        Cache::put('last_daily_activities_run', Carbon::yesterday()->setTime(0, 5, 0));
        
        $component = Livewire::test(EndOfDay::class);
        
        $lastRunDate = $component->get('lastRunDate');
        $this->assertNotNull($lastRunDate);
        
        // Should show yesterday's date
        $this->assertStringContainsString(Carbon::yesterday()->format('Y-m-d'), $lastRunDate);
    }

    /**
     * Test progress bar color classes
     */
    public function test_progress_bar_color_classes()
    {
        $component = Livewire::test(EndOfDay::class)->instance();
        
        // Test different progress levels
        $this->assertEquals('bg-green-500', $component->getProgressBarClass(80));
        $this->assertEquals('bg-green-500', $component->getProgressBarClass(100));
        $this->assertEquals('bg-yellow-500', $component->getProgressBarClass(60));
        $this->assertEquals('bg-yellow-500', $component->getProgressBarClass(70));
        $this->assertEquals('bg-red-500', $component->getProgressBarClass(30));
        $this->assertEquals('bg-red-500', $component->getProgressBarClass(0));
    }

    /**
     * Test status badge classes
     */
    public function test_status_badge_classes()
    {
        $component = Livewire::test(EndOfDay::class)->instance();
        
        $this->assertEquals('bg-green-50 text-green-700', $component->getStatusBadgeClass('completed'));
        $this->assertEquals('bg-blue-50 text-blue-700', $component->getStatusBadgeClass('running'));
        $this->assertEquals('bg-red-50 text-red-700', $component->getStatusBadgeClass('failed'));
        $this->assertEquals('bg-gray-50 text-gray-700', $component->getStatusBadgeClass('skipped'));
        $this->assertEquals('bg-yellow-50 text-yellow-700', $component->getStatusBadgeClass('pending'));
    }

    /**
     * Test status icons
     */
    public function test_status_icons()
    {
        $component = Livewire::test(EndOfDay::class)->instance();
        
        $this->assertEquals('✓', $component->getStatusIcon('completed'));
        $this->assertEquals('↻', $component->getStatusIcon('running'));
        $this->assertEquals('✗', $component->getStatusIcon('failed'));
        $this->assertEquals('−', $component->getStatusIcon('skipped'));
        $this->assertEquals('○', $component->getStatusIcon('pending'));
    }

    /**
     * Test refresh activities listener
     */
    public function test_refresh_activities_listener()
    {
        // Update an activity
        DailyActivityStatus::where('activity_key', 'inactive_members')
            ->whereDate('process_date', Carbon::today())
            ->update(['progress' => 75, 'status' => 'running']);
        
        $component = Livewire::test(EndOfDay::class);
        
        // Emit refresh event
        $component->emit('refreshActivities');
        
        // Check that activities are reloaded
        $activities = collect($component->get('activities'));
        $inactiveMembers = $activities->firstWhere('key', 'inactive_members');
        
        $this->assertEquals(75, $inactiveMembers['progress']);
        $this->assertEquals('running', $inactiveMembers['status']);
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