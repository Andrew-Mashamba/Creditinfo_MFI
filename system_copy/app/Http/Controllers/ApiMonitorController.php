<?php

namespace App\Http\Controllers;

use App\Services\ApiLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * API Monitoring Dashboard Controller
 * 
 * Provides real-time monitoring and analytics for API calls
 */
class ApiMonitorController extends Controller
{
    private $apiLogger;
    
    public function __construct()
    {
        $this->apiLogger = ApiLogger::getInstance();
    }
    
    /**
     * Display the API monitoring dashboard
     */
    public function index()
    {
        $date = now()->format('Y-m-d');
        
        // Get today's summary
        $summary = $this->apiLogger->generateDailySummary($date);
        
        // Get recent requests
        $recentRequests = $this->getRecentRequests(20);
        
        // Get performance metrics
        $metrics = $this->getPerformanceMetrics();
        
        // Get error statistics
        $errorStats = $this->getErrorStatistics();
        
        return view('api-monitor.dashboard', compact(
            'summary',
            'recentRequests',
            'metrics',
            'errorStats'
        ));
    }
    
    /**
     * Get detailed view of a specific request
     */
    public function show($requestId)
    {
        $pair = $this->apiLogger->getRequestResponsePair($requestId);
        
        if (!$pair['request']) {
            abort(404, 'Request not found');
        }
        
        return view('api-monitor.request-detail', compact('pair', 'requestId'));
    }
    
    /**
     * Get API logs for a specific date
     */
    public function logs(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $type = $request->get('type', 'all');
        $service = $request->get('service');
        
        $logs = $this->getLogsForDate($date, $type, $service);
        
        return view('api-monitor.logs', compact('logs', 'date', 'type', 'service'));
    }
    
    /**
     * Get performance metrics
     */
    public function metrics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $metrics = $this->getMetricsForDateRange($startDate, $endDate);
        
        return view('api-monitor.metrics', compact('metrics', 'startDate', 'endDate'));
    }
    
    /**
     * API endpoint for real-time data
     */
    public function realtime()
    {
        $data = [
            'timestamp' => now()->toIso8601String(),
            'recent_requests' => $this->getRecentRequests(10),
            'current_metrics' => $this->getCurrentMetrics(),
            'active_services' => $this->getActiveServices(),
            'error_rate' => $this->getCurrentErrorRate(),
        ];
        
        return response()->json($data);
    }
    
    /**
     * Export logs as CSV
     */
    public function export(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $type = $request->get('type', 'all');
        
        $logs = $this->getLogsForDate($date, $type);
        
        $csv = $this->generateCSV($logs);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="api_logs_' . $date . '.csv"');
    }
    
    /**
     * Get service health status
     */
    public function health()
    {
        $services = config('api-logging.services', []);
        $health = [];
        
        foreach ($services as $service => $config) {
            if (!is_array($config) || !($config['enabled'] ?? false)) {
                continue;
            }
            
            $health[$service] = $this->checkServiceHealth($service);
        }
        
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'services' => $health,
            'overall_status' => $this->getOverallHealth($health),
        ]);
    }
    
    /**
     * Get recent requests
     */
    private function getRecentRequests($limit = 20)
    {
        $date = now()->format('Y-m-d');
        $path = "api-logs/{$date}/requests.json";
        
        if (!Storage::exists($path)) {
            return [];
        }
        
        $content = Storage::get($path);
        $requests = json_decode($content, true) ?? [];
        
        // Get last N requests
        $recent = array_slice($requests, -$limit);
        
        // Reverse to show newest first
        return array_reverse($recent);
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        $date = now()->format('Y-m-d');
        $responsePath = "api-logs/{$date}/responses.json";
        
        if (!Storage::exists($responsePath)) {
            return [
                'avg_response_time' => 0,
                'min_response_time' => 0,
                'max_response_time' => 0,
                'p95_response_time' => 0,
                'p99_response_time' => 0,
                'total_requests' => 0,
            ];
        }
        
        $content = Storage::get($responsePath);
        $responses = json_decode($content, true) ?? [];
        
        $times = array_filter(array_column(array_column($responses, 'response'), 'response_time_ms'));
        
        if (empty($times)) {
            return [
                'avg_response_time' => 0,
                'min_response_time' => 0,
                'max_response_time' => 0,
                'p95_response_time' => 0,
                'p99_response_time' => 0,
                'total_requests' => count($responses),
            ];
        }
        
        sort($times);
        
        return [
            'avg_response_time' => round(array_sum($times) / count($times), 2),
            'min_response_time' => min($times),
            'max_response_time' => max($times),
            'p95_response_time' => $this->percentile($times, 0.95),
            'p99_response_time' => $this->percentile($times, 0.99),
            'total_requests' => count($responses),
        ];
    }
    
    /**
     * Get error statistics
     */
    private function getErrorStatistics()
    {
        $date = now()->format('Y-m-d');
        $errorPath = "api-logs/{$date}/errors.json";
        
        if (!Storage::exists($errorPath)) {
            return [
                'total_errors' => 0,
                'error_types' => [],
                'services_affected' => [],
            ];
        }
        
        $content = Storage::get($errorPath);
        $errors = json_decode($content, true) ?? [];
        
        $errorTypes = [];
        $servicesAffected = [];
        
        foreach ($errors as $error) {
            $type = $error['error']['message'] ?? 'Unknown';
            $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
            
            if (isset($error['context']['service'])) {
                $servicesAffected[$error['context']['service']] = true;
            }
        }
        
        return [
            'total_errors' => count($errors),
            'error_types' => $errorTypes,
            'services_affected' => array_keys($servicesAffected),
        ];
    }
    
    /**
     * Get logs for a specific date
     */
    private function getLogsForDate($date, $type = 'all', $service = null)
    {
        $logs = [];
        
        if ($type === 'all' || $type === 'requests') {
            $path = "api-logs/{$date}/requests.json";
            if (Storage::exists($path)) {
                $content = Storage::get($path);
                $requests = json_decode($content, true) ?? [];
                
                if ($service) {
                    $requests = array_filter($requests, function($req) use ($service) {
                        return ($req['service'] ?? '') === $service;
                    });
                }
                
                foreach ($requests as $req) {
                    $req['type'] = 'request';
                    $logs[] = $req;
                }
            }
        }
        
        if ($type === 'all' || $type === 'responses') {
            $path = "api-logs/{$date}/responses.json";
            if (Storage::exists($path)) {
                $content = Storage::get($path);
                $responses = json_decode($content, true) ?? [];
                
                foreach ($responses as $resp) {
                    $resp['type'] = 'response';
                    $logs[] = $resp;
                }
            }
        }
        
        if ($type === 'all' || $type === 'errors') {
            $path = "api-logs/{$date}/errors.json";
            if (Storage::exists($path)) {
                $content = Storage::get($path);
                $errors = json_decode($content, true) ?? [];
                
                foreach ($errors as $err) {
                    $err['type'] = 'error';
                    $logs[] = $err;
                }
            }
        }
        
        // Sort by timestamp
        usort($logs, function($a, $b) {
            return strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? '');
        });
        
        return $logs;
    }
    
    /**
     * Get metrics for date range
     */
    private function getMetricsForDateRange($startDate, $endDate)
    {
        $metrics = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $summary = $this->apiLogger->generateDailySummary($date);
            
            $metrics[$date] = [
                'date' => $date,
                'total_requests' => $summary['total_requests'],
                'success_rate' => $summary['success_rate'],
                'avg_response_time' => $summary['average_response_time_ms'],
                'errors' => $summary['total_errors'],
            ];
            
            $current->addDay();
        }
        
        return $metrics;
    }
    
    /**
     * Get current metrics (last hour)
     */
    private function getCurrentMetrics()
    {
        // This would typically query from a time-series database
        // For now, return mock data
        return [
            'requests_per_minute' => random_int(10, 50),
            'avg_response_time_ms' => random_int(100, 1000),
            'error_rate' => random_int(0, 5) / 100,
            'active_connections' => random_int(5, 20),
        ];
    }
    
    /**
     * Get active services
     */
    private function getActiveServices()
    {
        $services = [];
        $configs = config('api-logging.services', []);
        
        foreach ($configs as $service => $config) {
            if (is_array($config) && ($config['enabled'] ?? false)) {
                $services[] = $service;
            }
        }
        
        return $services;
    }
    
    /**
     * Get current error rate
     */
    private function getCurrentErrorRate()
    {
        $date = now()->format('Y-m-d');
        $summary = $this->apiLogger->generateDailySummary($date);
        
        if ($summary['total_requests'] === 0) {
            return 0;
        }
        
        return round(($summary['total_errors'] / $summary['total_requests']) * 100, 2);
    }
    
    /**
     * Check service health
     */
    private function checkServiceHealth($service)
    {
        $date = now()->format('Y-m-d');
        $requests = $this->getServiceRequests($service, $date);
        
        if (empty($requests)) {
            return [
                'status' => 'unknown',
                'last_request' => null,
                'success_rate' => 0,
                'avg_response_time' => 0,
            ];
        }
        
        $lastRequest = end($requests);
        $successCount = 0;
        $totalTime = 0;
        $count = 0;
        
        foreach ($requests as $req) {
            // Match with response
            $response = $this->findResponse($req['request_id'] ?? '');
            if ($response) {
                if ($response['response_received'] ?? false) {
                    $statusCode = $response['response']['status_code'] ?? 0;
                    if ($statusCode >= 200 && $statusCode < 300) {
                        $successCount++;
                    }
                    $totalTime += $response['response']['response_time_ms'] ?? 0;
                    $count++;
                }
            }
        }
        
        $successRate = $count > 0 ? round(($successCount / $count) * 100, 2) : 0;
        $avgTime = $count > 0 ? round($totalTime / $count, 2) : 0;
        
        $status = 'healthy';
        if ($successRate < 50) {
            $status = 'critical';
        } elseif ($successRate < 90) {
            $status = 'degraded';
        }
        
        return [
            'status' => $status,
            'last_request' => $lastRequest['timestamp'] ?? null,
            'success_rate' => $successRate,
            'avg_response_time' => $avgTime,
            'total_requests' => count($requests),
        ];
    }
    
    /**
     * Get service requests
     */
    private function getServiceRequests($service, $date)
    {
        $path = "api-logs/{$date}/requests.json";
        
        if (!Storage::exists($path)) {
            return [];
        }
        
        $content = Storage::get($path);
        $requests = json_decode($content, true) ?? [];
        
        return array_filter($requests, function($req) use ($service) {
            return ($req['service'] ?? '') === $service;
        });
    }
    
    /**
     * Find response by request ID
     */
    private function findResponse($requestId)
    {
        if (empty($requestId)) {
            return null;
        }
        
        $date = now()->format('Y-m-d');
        $path = "api-logs/{$date}/responses.json";
        
        if (!Storage::exists($path)) {
            return null;
        }
        
        $content = Storage::get($path);
        $responses = json_decode($content, true) ?? [];
        
        foreach ($responses as $response) {
            if (($response['request_id'] ?? '') === $requestId) {
                return $response;
            }
        }
        
        return null;
    }
    
    /**
     * Get overall health status
     */
    private function getOverallHealth($serviceHealth)
    {
        $statuses = array_column($serviceHealth, 'status');
        
        if (in_array('critical', $statuses)) {
            return 'critical';
        }
        
        if (in_array('degraded', $statuses)) {
            return 'degraded';
        }
        
        if (in_array('unknown', $statuses)) {
            return 'partial';
        }
        
        return 'healthy';
    }
    
    /**
     * Calculate percentile
     */
    private function percentile($array, $percentile)
    {
        $count = count($array);
        $index = ceil($percentile * $count) - 1;
        
        return $array[$index] ?? 0;
    }
    
    /**
     * Generate CSV from logs
     */
    private function generateCSV($logs)
    {
        $csv = "Timestamp,Type,Service,Operation,Method,URL,Status Code,Response Time (ms),Error\n";
        
        foreach ($logs as $log) {
            $timestamp = $log['timestamp'] ?? '';
            $type = $log['type'] ?? '';
            $service = $log['service'] ?? '';
            $operation = $log['operation'] ?? '';
            $method = $log['request']['method'] ?? '';
            $url = $log['request']['url'] ?? '';
            $statusCode = $log['response']['status_code'] ?? '';
            $responseTime = $log['response']['response_time_ms'] ?? '';
            $error = $log['error'] ?? '';
            
            $csv .= "\"{$timestamp}\",\"{$type}\",\"{$service}\",\"{$operation}\",\"{$method}\",\"{$url}\",\"{$statusCode}\",\"{$responseTime}\",\"{$error}\"\n";
        }
        
        return $csv;
    }
}