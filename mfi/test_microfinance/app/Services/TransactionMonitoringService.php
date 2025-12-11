<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionReversal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionMonitoringService
{
    /**
     * Get system health metrics
     */
    public function getSystemHealth()
    {
        $now = now();
        $today = $now->toDateString();
        $lastHour = $now->copy()->subHour();

        $metrics = [
            'timestamp' => $now->toIso8601String(),
            'overall_status' => 'healthy',
            'transactions' => $this->getTransactionMetrics($today, $lastHour),
            'reversals' => $this->getReversalMetrics($today, $lastHour),
            'queues' => $this->getQueueMetrics(),
            'circuit_breakers' => $this->getCircuitBreakerStatus(),
            'external_services' => $this->getExternalServiceHealth(),
            'alerts' => []
        ];

        // Check for alerts
        $alerts = $this->checkForAlerts($metrics);
        $metrics['alerts'] = $alerts;

        // Update overall status
        if (!empty($alerts['critical']) || !empty($alerts['high'])) {
            $metrics['overall_status'] = 'degraded';
        }
        if (!empty($alerts['critical'])) {
            $metrics['overall_status'] = 'critical';
        }

        return $metrics;
    }

    protected function getTransactionMetrics($today, $lastHour)
    {
        $todayStats = Transaction::whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = "suspect" THEN 1 ELSE 0 END) as suspect,
                AVG(CASE WHEN completed_at IS NOT NULL THEN TIMESTAMPDIFF(MICROSECOND, created_at, completed_at) / 1000 ELSE NULL END) as avg_response_time_ms
            ')
            ->first();
        $lastHourStats = Transaction::where('created_at', '>=', $lastHour)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing
            ')
            ->first();
        $todayFailureRate = $todayStats->total > 0 ? $todayStats->failed / $todayStats->total : 0;
        $lastHourFailureRate = $lastHourStats->total > 0 ? $lastHourStats->failed / $lastHourStats->total : 0;
        return [
            'today' => [
                'total' => $todayStats->total ?? 0,
                'completed' => $todayStats->completed ?? 0,
                'failed' => $todayStats->failed ?? 0,
                'processing' => $todayStats->processing ?? 0,
                'suspect' => $todayStats->suspect ?? 0,
                'failure_rate' => round($todayFailureRate * 100, 2),
                'avg_response_time_ms' => round($todayStats->avg_response_time_ms ?? 0, 2)
            ],
            'last_hour' => [
                'total' => $lastHourStats->total ?? 0,
                'completed' => $lastHourStats->completed ?? 0,
                'failed' => $lastHourStats->failed ?? 0,
                'processing' => $lastHourStats->processing ?? 0,
                'failure_rate' => round($lastHourFailureRate * 100, 2)
            ]
        ];
    }

    protected function getReversalMetrics($today, $lastHour)
    {
        $todayStats = TransactionReversal::whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "dead_letter" THEN 1 ELSE 0 END) as dead_letter,
                SUM(CASE WHEN is_automatic = true THEN 1 ELSE 0 END) as automatic,
                SUM(CASE WHEN is_automatic = false THEN 1 ELSE 0 END) as manual
            ')
            ->first();
        $lastHourStats = TransactionReversal::where('created_at', '>=', $lastHour)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
            ')
            ->first();
        $todayFailureRate = $todayStats->total > 0 ? $todayStats->failed / $todayStats->total : 0;
        $lastHourFailureRate = $lastHourStats->total > 0 ? $lastHourStats->failed / $lastHourStats->total : 0;
        return [
            'today' => [
                'total' => $todayStats->total ?? 0,
                'completed' => $todayStats->completed ?? 0,
                'failed' => $todayStats->failed ?? 0,
                'pending' => $todayStats->pending ?? 0,
                'dead_letter' => $todayStats->dead_letter ?? 0,
                'automatic' => $todayStats->automatic ?? 0,
                'manual' => $todayStats->manual ?? 0,
                'failure_rate' => round($todayFailureRate * 100, 2)
            ],
            'last_hour' => [
                'total' => $lastHourStats->total ?? 0,
                'completed' => $lastHourStats->completed ?? 0,
                'failed' => $lastHourStats->failed ?? 0,
                'failure_rate' => round($lastHourFailureRate * 100, 2)
            ]
        ];
    }

    protected function getQueueMetrics()
    {
        $pendingTransactions = Transaction::where('status', 'processing')->count();
        $pendingReversals = TransactionReversal::where('status', 'pending')->count();
        $failedTransactions = Transaction::where('status', 'failed')->where('retry_count', '<', 3)->count();
        return [
            'transactions' => [
                'pending' => $pendingTransactions,
                'failed_retryable' => $failedTransactions
            ],
            'reversals' => [
                'pending' => $pendingReversals
            ],
            'notifications' => [
                'pending' => 0 // Would get from actual queue
            ]
        ];
    }

    protected function getCircuitBreakerStatus()
    {
        $externalSystems = ['tips_mno', 'tips_bank', 'internal_transfer'];
        $circuitBreakers = [];
        foreach ($externalSystems as $system) {
            $key = "circuit_breaker:transaction:{$system}";
            $reversalKey = "circuit_breaker:reversal:{$system}";
            $transactionBreaker = Cache::get($key, ['state' => 'closed']);
            $reversalBreaker = Cache::get($reversalKey, ['state' => 'closed']);
            $circuitBreakers[$system] = [
                'transactions' => $transactionBreaker['state'],
                'reversals' => $reversalBreaker['state'],
                'last_failure_time' => $transactionBreaker['last_failure_time'] ?? null
            ];
        }
        return $circuitBreakers;
    }

    protected function getExternalServiceHealth()
    {
        $lastHour = now()->subHour();
        $services = ['tips_mno', 'tips_bank', 'internal_transfer'];
        $health = [];
        foreach ($services as $service) {
            $stats = Transaction::where('external_system', $service)
                ->where('created_at', '>=', $lastHour)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful,
                    AVG(CASE WHEN completed_at IS NOT NULL THEN TIMESTAMPDIFF(MICROSECOND, created_at, completed_at) / 1000 ELSE NULL END) as avg_response_time_ms
                ')
                ->first();
            $successRate = $stats->total > 0 ? $stats->successful / $stats->total : 1;
            $avgResponseTime = $stats->avg_response_time_ms ?? 0;
            $health[$service] = [
                'status' => $successRate > 0.9 ? 'healthy' : ($successRate > 0.7 ? 'degraded' : 'unhealthy'),
                'success_rate' => round($successRate * 100, 2),
                'avg_response_time_ms' => round($avgResponseTime, 2),
                'total_requests' => $stats->total ?? 0
            ];
        }
        return $health;
    }

    protected function checkForAlerts($metrics)
    {
        $alerts = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        if ($metrics['transactions']['today']['failure_rate'] > 20) {
            $alerts['critical'][] = [
                'type' => 'high_failure_rate',
                'message' => "Transaction failure rate is {$metrics['transactions']['today']['failure_rate']}%",
                'metric' => 'transaction_failure_rate',
                'value' => $metrics['transactions']['today']['failure_rate']
            ];
        } elseif ($metrics['transactions']['today']['failure_rate'] > 10) {
            $alerts['high'][] = [
                'type' => 'elevated_failure_rate',
                'message' => "Transaction failure rate is {$metrics['transactions']['today']['failure_rate']}%",
                'metric' => 'transaction_failure_rate',
                'value' => $metrics['transactions']['today']['failure_rate']
            ];
        }
        if ($metrics['transactions']['today']['avg_response_time_ms'] > 60000) {
            $alerts['critical'][] = [
                'type' => 'slow_response_time',
                'message' => "Average response time is {$metrics['transactions']['today']['avg_response_time_ms']}ms",
                'metric' => 'response_time',
                'value' => $metrics['transactions']['today']['avg_response_time_ms']
            ];
        }
        foreach ($metrics['circuit_breakers'] as $system => $breaker) {
            if ($breaker['transactions'] === 'open' || $breaker['reversals'] === 'open') {
                $alerts['high'][] = [
                    'type' => 'circuit_breaker_open',
                    'message' => "Circuit breaker is open for {$system}",
                    'metric' => 'circuit_breaker',
                    'system' => $system
                ];
            }
        }
        foreach ($metrics['external_services'] as $service => $health) {
            if ($health['status'] === 'unhealthy') {
                $alerts['critical'][] = [
                    'type' => 'external_service_unhealthy',
                    'message' => "External service {$service} is unhealthy (success rate: {$health['success_rate']}%)",
                    'metric' => 'external_service_health',
                    'service' => $service,
                    'success_rate' => $health['success_rate']
                ];
            } elseif ($health['status'] === 'degraded') {
                $alerts['high'][] = [
                    'type' => 'external_service_degraded',
                    'message' => "External service {$service} is degraded (success rate: {$health['success_rate']}%)",
                    'metric' => 'external_service_health',
                    'service' => $service,
                    'success_rate' => $health['success_rate']
                ];
            }
        }
        return $alerts;
    }

    /**
     * Generate reconciliation report
     */
    public function generateReconciliationReport($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateString = $date->toDateString();
        $report = [
            'report_date' => $dateString,
            'generated_at' => now()->toIso8601String(),
            'summary' => $this->getReconciliationSummary($date),
            'transactions' => $this->getReconciliationTransactions($date),
            'reversals' => $this->getReconciliationReversals($date),
            'discrepancies' => $this->findDiscrepancies($date),
            'recommendations' => []
        ];
        if (!empty($report['discrepancies'])) {
            $report['recommendations'][] = 'Review and resolve transaction discrepancies';
        }
        if ($report['summary']['failed_transactions'] > 0) {
            $report['recommendations'][] = 'Investigate failed transactions';
        }
        if ($report['summary']['suspect_transactions'] > 0) {
            $report['recommendations'][] = 'Review suspect transactions for manual intervention';
        }
        return $report;
    }

    protected function getReconciliationSummary($date)
    {
        $transactions = Transaction::whereDate('created_at', $date)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "suspect" THEN 1 ELSE 0 END) as suspect,
                SUM(CASE WHEN status = "reversed" THEN 1 ELSE 0 END) as reversed,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as completed_amount
            ')
            ->first();
        $reversals = TransactionReversal::whereDate('created_at', $date)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed
            ')
            ->first();
        return [
            'total_transactions' => $transactions->total ?? 0,
            'completed_transactions' => $transactions->completed ?? 0,
            'failed_transactions' => $transactions->failed ?? 0,
            'suspect_transactions' => $transactions->suspect ?? 0,
            'reversed_transactions' => $transactions->reversed ?? 0,
            'total_amount' => $transactions->total_amount ?? 0,
            'completed_amount' => $transactions->completed_amount ?? 0,
            'total_reversals' => $reversals->total ?? 0,
            'completed_reversals' => $reversals->completed ?? 0,
            'failed_reversals' => $reversals->failed ?? 0
        ];
    }

    protected function getReconciliationTransactions($date)
    {
        return Transaction::whereDate('created_at', $date)
            ->whereNotNull('external_reference')
            ->select([
                'id', 'reference', 'external_reference', 'amount', 'status',
                'external_system', 'created_at', 'completed_at', 'failed_at'
            ])
            ->orderBy('created_at')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'external_system' => $transaction->external_system,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'completed_at' => $transaction->completed_at?->toIso8601String(),
                    'failed_at' => $transaction->failed_at?->toIso8601String()
                ];
            });
    }

    protected function getReconciliationReversals($date)
    {
        return TransactionReversal::whereDate('created_at', $date)
            ->whereNotNull('external_reference')
            ->with('transaction')
            ->select([
                'id', 'reversal_reference', 'external_reference', 'status',
                'created_at', 'completed_at', 'failed_at', 'transaction_id'
            ])
            ->orderBy('created_at')
            ->get()
            ->map(function ($reversal) {
                return [
                    'id' => $reversal->id,
                    'reversal_reference' => $reversal->reversal_reference,
                    'external_reference' => $reversal->external_reference,
                    'status' => $reversal->status,
                    'original_transaction_reference' => $reversal->transaction->reference ?? null,
                    'amount' => $reversal->transaction->amount ?? null,
                    'created_at' => $reversal->created_at->toIso8601String(),
                    'completed_at' => $reversal->completed_at?->toIso8601String(),
                    'failed_at' => $reversal->failed_at?->toIso8601String()
                ];
            });
    }

    protected function findDiscrepancies($date)
    {
        $discrepancies = [];
        $transactionsWithoutExternalRef = Transaction::whereDate('created_at', $date)
            ->whereNull('external_reference')
            ->where('external_system', '!=', 'internal_transfer')
            ->count();
        if ($transactionsWithoutExternalRef > 0) {
            $discrepancies[] = [
                'type' => 'missing_external_reference',
                'count' => $transactionsWithoutExternalRef,
                'description' => 'Transactions without external reference numbers'
            ];
        }
        $suspectTransactions = Transaction::whereDate('created_at', $date)
            ->where('status', 'suspect')
            ->count();
        if ($suspectTransactions > 0) {
            $discrepancies[] = [
                'type' => 'suspect_transactions',
                'count' => $suspectTransactions,
                'description' => 'Transactions with unclear status requiring manual review'
            ];
        }
        $failedReversals = TransactionReversal::whereDate('created_at', $date)
            ->where('status', 'failed')
            ->count();
        if ($failedReversals > 0) {
            $discrepancies[] = [
                'type' => 'failed_reversals',
                'count' => $failedReversals,
                'description' => 'Failed reversal attempts requiring attention'
            ];
        }
        return $discrepancies;
    }

    /**
     * Send monitoring alerts
     */
    public function sendMonitoringAlerts($metrics)
    {
        if (empty($metrics['alerts']['critical']) && empty($metrics['alerts']['high'])) {
            return;
        }
        $alertData = [
            'timestamp' => now()->toIso8601String(),
            'system_status' => $metrics['overall_status'],
            'critical_alerts' => $metrics['alerts']['critical'],
            'high_alerts' => $metrics['alerts']['high'],
            'metrics_summary' => [
                'transaction_failure_rate' => $metrics['transactions']['today']['failure_rate'],
                'avg_response_time' => $metrics['transactions']['today']['avg_response_time_ms'],
                'pending_transactions' => $metrics['queues']['transactions']['pending']
            ]
        ];
        try {
            \App\Jobs\SendTransactionNotification::dispatch(
                null,
                'system_alert',
                $alertData
            )->onQueue('notifications');
        } catch (\Exception $e) {
            Log::error('Failed to send monitoring alert', [
                'error' => $e->getMessage(),
                'alerts' => $metrics['alerts']
            ]);
        }
    }
}
