<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

class EmailTrackingService
{
    /**
     * Enable tracking for an email
     */
    public function enableTracking($emailId, $trackOpens = true, $trackClicks = true)
    {
        try {
            $trackingId = Str::uuid()->toString();
            $pixelId = Str::random(32);
            
            // Create tracking record
            DB::table('email_tracking')->insert([
                'email_id' => $emailId,
                'tracking_id' => $trackingId,
                'track_opens' => $trackOpens,
                'track_clicks' => $trackClicks,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update email record
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'tracking_enabled' => true,
                    'tracking_pixel_id' => $pixelId
                ]);
                
            return [
                'success' => true,
                'tracking_id' => $trackingId,
                'pixel_id' => $pixelId
            ];
        } catch (\Exception $e) {
            Log::error('Failed to enable email tracking: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to enable tracking'
            ];
        }
    }
    
    /**
     * Add tracking pixel to email body
     */
    public function addTrackingPixel($body, $pixelId)
    {
        $pixelUrl = route('email.tracking.pixel', ['id' => $pixelId]);
        $pixel = '<img src="' . $pixelUrl . '" width="1" height="1" border="0" style="display:block;" alt="" />';
        
        // Add pixel at the end of the email body
        if (stripos($body, '</body>') !== false) {
            $body = str_ireplace('</body>', $pixel . '</body>', $body);
        } else {
            $body .= $pixel;
        }
        
        return $body;
    }
    
    /**
     * Track email open
     */
    public function trackOpen($pixelId)
    {
        try {
            // Find email by pixel ID
            $email = DB::table('emails')
                ->where('tracking_pixel_id', $pixelId)
                ->first();
                
            if (!$email) {
                return false;
            }
            
            // Get tracking record
            $tracking = DB::table('email_tracking')
                ->where('email_id', $email->id)
                ->first();
                
            if (!$tracking || !$tracking->track_opens) {
                return false;
            }
            
            // Get request details
            $openDetails = json_decode($tracking->open_details ?? '[]', true);
            $openDetails[] = [
                'timestamp' => now()->toIso8601String(),
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'referer' => Request::header('referer')
            ];
            
            // Update tracking record
            DB::table('email_tracking')
                ->where('id', $tracking->id)
                ->update([
                    'open_count' => DB::raw('open_count + 1'),
                    'first_opened_at' => $tracking->first_opened_at ?? now(),
                    'last_opened_at' => now(),
                    'open_details' => json_encode($openDetails),
                    'updated_at' => now()
                ]);
                
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track email open: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track link click
     */
    public function trackClick($trackingId, $linkUrl)
    {
        try {
            $tracking = DB::table('email_tracking')
                ->where('tracking_id', $trackingId)
                ->first();
                
            if (!$tracking || !$tracking->track_clicks) {
                return false;
            }
            
            // Get existing clicks
            $linkClicks = json_decode($tracking->link_clicks ?? '[]', true);
            $linkClicks[] = [
                'url' => $linkUrl,
                'timestamp' => now()->toIso8601String(),
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent()
            ];
            
            // Update tracking record
            DB::table('email_tracking')
                ->where('id', $tracking->id)
                ->update([
                    'link_clicks' => json_encode($linkClicks),
                    'updated_at' => now()
                ]);
                
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track link click: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Wrap links in email for tracking
     */
    public function wrapLinksForTracking($body, $trackingId)
    {
        // Find all links in the email body
        $pattern = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/i';
        
        $body = preg_replace_callback($pattern, function($matches) use ($trackingId) {
            $originalUrl = $matches[2];
            
            // Skip mailto links and anchors
            if (strpos($originalUrl, 'mailto:') === 0 || strpos($originalUrl, '#') === 0) {
                return $matches[0];
            }
            
            // Create tracking URL
            $trackingUrl = route('email.tracking.click', [
                'tracking_id' => $trackingId,
                'url' => base64_encode($originalUrl)
            ]);
            
            return str_replace($originalUrl, $trackingUrl, $matches[0]);
        }, $body);
        
        return $body;
    }
    
    /**
     * Get tracking statistics for an email
     */
    public function getTrackingStats($emailId)
    {
        try {
            $tracking = DB::table('email_tracking')
                ->where('email_id', $emailId)
                ->first();
                
            if (!$tracking) {
                return null;
            }
            
            $linkClicks = json_decode($tracking->link_clicks ?? '[]', true);
            $uniqueLinks = array_unique(array_column($linkClicks, 'url'));
            
            return [
                'tracking_id' => $tracking->tracking_id,
                'track_opens' => $tracking->track_opens,
                'track_clicks' => $tracking->track_clicks,
                'open_count' => $tracking->open_count,
                'first_opened_at' => $tracking->first_opened_at,
                'last_opened_at' => $tracking->last_opened_at,
                'total_clicks' => count($linkClicks),
                'unique_clicks' => count($uniqueLinks),
                'clicked_links' => $this->aggregateClickedLinks($linkClicks)
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get tracking stats: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Aggregate clicked links data
     */
    protected function aggregateClickedLinks($linkClicks)
    {
        $aggregated = [];
        
        foreach ($linkClicks as $click) {
            $url = $click['url'];
            if (!isset($aggregated[$url])) {
                $aggregated[$url] = [
                    'url' => $url,
                    'click_count' => 0,
                    'first_clicked' => $click['timestamp'],
                    'last_clicked' => $click['timestamp']
                ];
            }
            
            $aggregated[$url]['click_count']++;
            $aggregated[$url]['last_clicked'] = $click['timestamp'];
        }
        
        return array_values($aggregated);
    }
    
    /**
     * Get tracking summary for multiple emails
     */
    public function getTrackingSummary($emailIds)
    {
        return DB::table('email_tracking')
            ->whereIn('email_id', $emailIds)
            ->select(
                'email_id',
                'open_count',
                'first_opened_at',
                'last_opened_at',
                DB::raw('JSON_LENGTH(link_clicks) as total_clicks')
            )
            ->get()
            ->keyBy('email_id');
    }
}