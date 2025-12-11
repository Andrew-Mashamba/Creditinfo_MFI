<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailTrackingService;

class EmailTrackingController extends Controller
{
    protected $trackingService;
    
    public function __construct(EmailTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }
    
    /**
     * Track email open via pixel
     */
    public function trackPixel($id)
    {
        // Track the open
        $this->trackingService->trackOpen($id);
        
        // Return a 1x1 transparent pixel
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        
        return response($pixel)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }
    
    /**
     * Track link click and redirect
     */
    public function trackClick($tracking_id, Request $request)
    {
        $url = base64_decode($request->get('url', ''));
        
        if (!$url) {
            return redirect('/');
        }
        
        // Track the click
        $this->trackingService->trackClick($tracking_id, $url);
        
        // Redirect to the original URL
        return redirect()->away($url);
    }
}