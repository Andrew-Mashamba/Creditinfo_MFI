<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\DailyActivityStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DailyActivityStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a new activity status
     */
    public function test_can_create_activity_status()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_activity',
            'activity_name' => 'Test Activity',
            'status' => 'pending',
            'progress' => 0,
            'process_date' => Carbon::today()
        ]);

        $this->assertDatabaseHas('daily_activity_status', [
            'activity_key' => 'test_activity',
            'activity_name' => 'Test Activity',
            'status' => 'pending'
        ]);

        $this->assertEquals('Test Activity', $activity->activity_name);
        $this->assertEquals('pending', $activity->status);
        $this->assertEquals(0, $activity->progress);
    }

    /**
     * Test get or create activity
     */
    public function test_get_or_create_activity()
    {
        // First call should create
        $activity1 = DailyActivityStatus::getOrCreateActivity('loan_processing', 'Loan Processing');
        $this->assertNotNull($activity1);
        $this->assertEquals('loan_processing', $activity1->activity_key);
        
        // Second call should get existing
        $activity2 = DailyActivityStatus::getOrCreateActivity('loan_processing', 'Loan Processing');
        $this->assertEquals($activity1->id, $activity2->id);
    }

    /**
     * Test getting today's activities
     */
    public function test_get_today_activities()
    {
        // Create activities for today
        DailyActivityStatus::create([
            'activity_key' => 'activity_1',
            'activity_name' => 'Activity 1',
            'status' => 'completed',
            'progress' => 100,
            'process_date' => Carbon::today()
        ]);

        DailyActivityStatus::create([
            'activity_key' => 'activity_2',
            'activity_name' => 'Activity 2',
            'status' => 'running',
            'progress' => 50,
            'process_date' => Carbon::today()
        ]);

        // Create activity for yesterday (should not be included)
        DailyActivityStatus::create([
            'activity_key' => 'activity_3',
            'activity_name' => 'Activity 3',
            'status' => 'completed',
            'progress' => 100,
            'process_date' => Carbon::yesterday()
        ]);

        $todayActivities = DailyActivityStatus::getTodayActivities();
        
        $this->assertCount(2, $todayActivities);
        $this->assertTrue($todayActivities->contains('activity_key', 'activity_1'));
        $this->assertTrue($todayActivities->contains('activity_key', 'activity_2'));
        $this->assertFalse($todayActivities->contains('activity_key', 'activity_3'));
    }

    /**
     * Test starting an activity
     */
    public function test_start_activity()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_start',
            'activity_name' => 'Test Start',
            'status' => 'pending',
            'progress' => 0,
            'process_date' => Carbon::today()
        ]);

        $activity->start('manual');

        $this->assertEquals('running', $activity->status);
        $this->assertEquals('manual', $activity->triggered_by);
        $this->assertNotNull($activity->started_at);
        $this->assertNull($activity->last_error);
    }

    /**
     * Test updating activity progress
     */
    public function test_update_activity_progress()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_progress',
            'activity_name' => 'Test Progress',
            'status' => 'running',
            'progress' => 0,
            'process_date' => Carbon::today()
        ]);

        $activity->updateProgress(50, 100, 5);

        $this->assertEquals(50, $activity->processed_records);
        $this->assertEquals(100, $activity->total_records);
        $this->assertEquals(5, $activity->failed_records);
        $this->assertEquals(50, $activity->progress);

        // Test with zero total
        $activity->updateProgress(0, 0, 0);
        $this->assertEquals(0, $activity->progress);
    }

    /**
     * Test completing an activity
     */
    public function test_complete_activity()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_complete',
            'activity_name' => 'Test Complete',
            'status' => 'running',
            'progress' => 50,
            'started_at' => Carbon::now()->subMinutes(5),
            'process_date' => Carbon::today()
        ]);

        $activity->complete();

        $this->assertEquals('completed', $activity->status);
        $this->assertEquals(100, $activity->progress);
        $this->assertNotNull($activity->completed_at);
        $this->assertNotNull($activity->execution_time_seconds);
        $this->assertGreaterThan(0, $activity->execution_time_seconds);
    }

    /**
     * Test failing an activity
     */
    public function test_fail_activity()
    {
        $activity = DailyActivityStatus::create([
            'activity_key' => 'test_fail',
            'activity_name' => 'Test Fail',
            'status' => 'running',
            'progress' => 30,
            'started_at' => Carbon::now()->subMinutes(2),
            'process_date' => Carbon::today()
        ]);

        $errorMessage = 'Database connection failed';
        $activity->fail($errorMessage);

        $this->assertEquals('failed', $activity->status);
        $this->assertEquals($errorMessage, $activity->last_error);
        $this->assertNotNull($activity->completed_at);
        $this->assertNotNull($activity->execution_time_seconds);
    }

    /**
     * Test status color attribute
     */
    public function test_status_color_attribute()
    {
        $activity = new DailyActivityStatus();
        
        $activity->status = 'completed';
        $this->assertEquals('green', $activity->status_color);
        
        $activity->status = 'running';
        $this->assertEquals('blue', $activity->status_color);
        
        $activity->status = 'failed';
        $this->assertEquals('red', $activity->status_color);
        
        $activity->status = 'skipped';
        $this->assertEquals('gray', $activity->status_color);
        
        $activity->status = 'pending';
        $this->assertEquals('yellow', $activity->status_color);
    }

    /**
     * Test formatted execution time
     */
    public function test_formatted_execution_time()
    {
        $activity = new DailyActivityStatus();
        
        // Test N/A for null
        $activity->execution_time_seconds = null;
        $this->assertEquals('N/A', $activity->formatted_execution_time);
        
        // Test seconds only
        $activity->execution_time_seconds = 45;
        $this->assertEquals('45s', $activity->formatted_execution_time);
        
        // Test minutes and seconds
        $activity->execution_time_seconds = 125;
        $this->assertEquals('2m 5s', $activity->formatted_execution_time);
        
        // Test exact minute
        $activity->execution_time_seconds = 60;
        $this->assertEquals('1m 0s', $activity->formatted_execution_time);
    }
}