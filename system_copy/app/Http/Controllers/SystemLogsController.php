<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SystemLogsController extends Controller
{
    public function index(Request $request)
    {
        $days = $request->input('days', 7);
        $resource = $request->input('resource');
        $format = $request->input('format', 'table');

        $logs = $this->getLogs($days, $resource);

        if ($format === 'json') {
            return response()->json($logs);
        }

        $resources = [
            'cpu' => 'CPU Usage',
            'memory' => 'Memory Usage',
            'network' => 'Network Status',
            'storage' => 'Storage Usage',
            'web-server' => 'Web Server Status'
        ];

        return view('system.logs.index', compact('logs', 'resources', 'days', 'resource'));
    }

    protected function getLogs($days, $resource = null)
    {
        $disk = Storage::disk('local');
        $path = 'logs/';
        $files = $disk->files($path);
        
        $logs = [];
        $cutoffDate = Carbon::now()->subDays($days);

        foreach ($files as $file) {
            if (!preg_match('/\.log$/', $file)) {
                continue;
            }

            $fileDate = Carbon::createFromTimestamp($disk->lastModified($file));
            if ($fileDate->lt($cutoffDate)) {
                continue;
            }

            $content = $disk->get($file);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                $logEntry = json_decode($line, true);
                if (!$logEntry) {
                    continue;
                }

                if ($resource && !isset($logEntry[$resource])) {
                    continue;
                }

                $logs[] = $logEntry;
            }
        }

        return $logs;
    }

    protected function getStatus($resource, $value)
    {
        $threshold = 80; // Default threshold
        
        if ($resource === 'network' && $value === 'disconnected') {
            return 'Critical';
        }

        if ($resource === 'web_server' && $value !== 'active') {
            return 'Critical';
        }

        if (is_numeric($value) && $value > $threshold) {
            return 'Warning';
        }

        return 'Normal';
    }
} 