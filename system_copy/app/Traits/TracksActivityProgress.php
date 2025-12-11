<?php

namespace App\Traits;

use App\Models\DailyActivityStatus;
use Illuminate\Support\Facades\Log;

trait TracksActivityProgress
{
    protected $currentActivity = null;

    /**
     * Start tracking an activity
     */
    protected function startActivity($key, $name, $triggeredBy = 'system')
    {
        try {
            $this->currentActivity = DailyActivityStatus::getOrCreateActivity($key, $name);
            $this->currentActivity->start($triggeredBy);
            
            Log::info("Started activity: {$name}");
        } catch (\Exception $e) {
            Log::error("Failed to start activity tracking: " . $e->getMessage());
        }
    }

    /**
     * Update activity progress
     */
    protected function updateActivityProgress($processed, $total, $failed = 0)
    {
        try {
            if ($this->currentActivity) {
                $this->currentActivity->updateProgress($processed, $total, $failed);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update activity progress: " . $e->getMessage());
        }
    }

    /**
     * Complete an activity
     */
    protected function completeActivity()
    {
        try {
            if ($this->currentActivity) {
                $this->currentActivity->complete();
                Log::info("Completed activity: {$this->currentActivity->activity_name}");
                $this->currentActivity = null;
            }
        } catch (\Exception $e) {
            Log::error("Failed to complete activity: " . $e->getMessage());
        }
    }

    /**
     * Mark activity as failed
     */
    protected function failActivity($error = null)
    {
        try {
            if ($this->currentActivity) {
                $this->currentActivity->fail($error);
                Log::error("Activity failed: {$this->currentActivity->activity_name} - {$error}");
                $this->currentActivity = null;
            }
        } catch (\Exception $e) {
            Log::error("Failed to mark activity as failed: " . $e->getMessage());
        }
    }

    /**
     * Track a simple activity without progress
     */
    protected function trackSimpleActivity($key, $name, callable $callback, $triggeredBy = 'system')
    {
        $this->startActivity($key, $name, $triggeredBy);
        
        try {
            $result = $callback();
            $this->completeActivity();
            return $result;
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            throw $e;
        }
    }
}