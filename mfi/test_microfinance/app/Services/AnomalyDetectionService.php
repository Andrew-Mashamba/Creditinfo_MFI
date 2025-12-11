<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Anomaly Detection Service
 *
 * Machine Learning-based anomaly detection for transactions
 * Uses statistical methods (Z-score, standard deviation) and pattern analysis
 *
 * Features:
 * - Transaction amount anomaly detection
 * - Time-based anomaly detection
 * - Velocity anomaly detection
 * - Pattern recognition
 */
class AnomalyDetectionService
{
    /**
     * Detect transaction anomaly
     *
     * @param array $transaction Current transaction data
     * @param string $accountNumber Account to analyze
     * @param string $serviceType Type of service
     * @return array Anomaly detection results
     */
    public function detectTransactionAnomaly(array $transaction, string $accountNumber, string $serviceType = 'all'): array
    {
        // Get historical data for this account
        $history = $this->getAccountHistory($accountNumber, $serviceType);

        // Need at least 10 transactions for meaningful analysis
        if (count($history) < 10) {
            return [
                'is_anomaly' => false,
                'anomaly_score' => 0,
                'reason' => 'Insufficient historical data (need 10+ transactions)',
                'confidence' => 'low',
            ];
        }

        // Perform multiple anomaly checks
        $amountAnomaly = $this->detectAmountAnomaly($transaction, $history);
        $timeAnomaly = $this->detectTimeAnomaly($transaction, $history);
        $velocityAnomaly = $this->detectVelocityAnomaly($accountNumber);
        $patternAnomaly = $this->detectPatternAnomaly($transaction, $history);

        // Combine scores
        $totalScore = max(
            $amountAnomaly['score'],
            $timeAnomaly['score'],
            $velocityAnomaly['score'],
            $patternAnomaly['score']
        );

        $isAnomaly = $totalScore >= 60; // Threshold for anomaly

        // Collect all reasons
        $reasons = array_filter([
            $amountAnomaly['is_anomaly'] ? $amountAnomaly['reason'] : null,
            $timeAnomaly['is_anomaly'] ? $timeAnomaly['reason'] : null,
            $velocityAnomaly['is_anomaly'] ? $velocityAnomaly['reason'] : null,
            $patternAnomaly['is_anomaly'] ? $patternAnomaly['reason'] : null,
        ]);

        return [
            'is_anomaly' => $isAnomaly,
            'anomaly_score' => $totalScore,
            'confidence' => $this->getConfidence(count($history), $totalScore),
            'reason' => implode('; ', $reasons),
            'details' => [
                'amount_anomaly' => $amountAnomaly,
                'time_anomaly' => $timeAnomaly,
                'velocity_anomaly' => $velocityAnomaly,
                'pattern_anomaly' => $patternAnomaly,
            ],
            'historical_count' => count($history),
        ];
    }

    /**
     * Detect amount anomaly using Z-score
     */
    protected function detectAmountAnomaly(array $transaction, array $history): array
    {
        $amounts = array_column($history, 'amount');

        // Calculate mean and standard deviation
        $mean = array_sum($amounts) / count($amounts);
        $stdDev = $this->standardDeviation($amounts, $mean);

        // Handle case where all amounts are the same
        if ($stdDev == 0) {
            $stdDev = 1;
        }

        // Calculate Z-score for current transaction
        $currentAmount = floatval($transaction['amount']);
        $zScore = ($currentAmount - $mean) / $stdDev;

        // Anomaly if Z-score > 3 (3 standard deviations from mean)
        $isAnomaly = abs($zScore) > 3;

        return [
            'is_anomaly' => $isAnomaly,
            'score' => min(abs($zScore) * 20, 100), // Scale to 0-100
            'z_score' => round($zScore, 2),
            'mean' => round($mean, 2),
            'std_dev' => round($stdDev, 2),
            'current_amount' => $currentAmount,
            'reason' => $isAnomaly
                ? "Amount ({$currentAmount}) is " . abs(round($zScore, 1)) . " std devs from mean ({$mean})"
                : null,
        ];
    }

    /**
     * Detect time-based anomaly
     */
    protected function detectTimeAnomaly(array $transaction, array $history): array
    {
        $currentHour = isset($transaction['hour']) ? (int)$transaction['hour'] : (int)date('H');
        $currentDay = isset($transaction['day_of_week']) ? (int)$transaction['day_of_week'] : (int)date('N');

        // Get historical hours and days
        $historicalHours = array_map(function($t) {
            return (int)date('H', strtotime($t['created_at']));
        }, $history);

        $historicalDays = array_map(function($t) {
            return (int)date('N', strtotime($t['created_at']));
        }, $history);

        // Calculate hour frequency
        $hourMatches = array_filter($historicalHours, function($h) use ($currentHour) {
            return abs($h - $currentHour) <= 2; // Within 2 hours
        });
        $hourFrequency = count($hourMatches) / count($historicalHours);

        // Calculate day frequency
        $dayMatches = array_filter($historicalDays, function($d) use ($currentDay) {
            return $d == $currentDay;
        });
        $dayFrequency = count($dayMatches) / count($historicalDays);

        // Anomaly if this hour/day is very rare
        $isHourAnomaly = $hourFrequency < 0.1 && ($currentHour < 6 || $currentHour > 22);
        $isDayAnomaly = $dayFrequency < 0.05; // Less than 5% of transactions on this day

        $isAnomaly = $isHourAnomaly || $isDayAnomaly;

        $score = 0;
        if ($isHourAnomaly) $score += 30;
        if ($isDayAnomaly) $score += 20;

        $reasons = [];
        if ($isHourAnomaly) {
            $reasons[] = "Unusual hour ({$currentHour}:00, only " . round($hourFrequency * 100, 1) . "% of transactions)";
        }
        if ($isDayAnomaly) {
            $reasons[] = "Unusual day of week (day {$currentDay}, only " . round($dayFrequency * 100, 1) . "% of transactions)";
        }

        return [
            'is_anomaly' => $isAnomaly,
            'score' => $score,
            'hour' => $currentHour,
            'hour_frequency' => round($hourFrequency * 100, 1),
            'day' => $currentDay,
            'day_frequency' => round($dayFrequency * 100, 1),
            'reason' => implode('; ', $reasons) ?: null,
        ];
    }

    /**
     * Detect velocity anomaly
     */
    protected function detectVelocityAnomaly(string $accountNumber): array
    {
        // Check transaction velocity (frequency)
        $lastHour = DB::table('api_transactions')
            ->where('source_account', $accountNumber)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $lastDay = DB::table('api_transactions')
            ->where('source_account', $accountNumber)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Calculate average from history (last 30 days)
        $totalLast30Days = DB::table('api_transactions')
            ->where('source_account', $accountNumber)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $avgPerDay = $totalLast30Days / 30;
        $avgPerHour = $avgPerDay / 24;

        // Anomaly if current rate is significantly higher than average
        $hourRatio = $avgPerHour > 0 ? ($lastHour / $avgPerHour) : 0;
        $dayRatio = $avgPerDay > 0 ? ($lastDay / $avgPerDay) : 0;

        $isAnomaly = $hourRatio > 5 || $dayRatio > 3; // 5x hourly or 3x daily average

        $score = 0;
        if ($hourRatio > 10) {
            $score = 80;
        } elseif ($hourRatio > 5) {
            $score = 50;
        } elseif ($dayRatio > 5) {
            $score = 60;
        } elseif ($dayRatio > 3) {
            $score = 40;
        }

        $reasons = [];
        if ($hourRatio > 5) {
            $reasons[] = "High hourly velocity ({$lastHour} transactions, " . round($hourRatio, 1) . "x average)";
        }
        if ($dayRatio > 3) {
            $reasons[] = "High daily velocity ({$lastDay} transactions, " . round($dayRatio, 1) . "x average)";
        }

        return [
            'is_anomaly' => $isAnomaly,
            'score' => $score,
            'last_hour_count' => $lastHour,
            'last_day_count' => $lastDay,
            'avg_per_hour' => round($avgPerHour, 2),
            'avg_per_day' => round($avgPerDay, 2),
            'hour_ratio' => round($hourRatio, 2),
            'day_ratio' => round($dayRatio, 2),
            'reason' => implode('; ', $reasons) ?: null,
        ];
    }

    /**
     * Detect pattern anomaly
     */
    protected function detectPatternAnomaly(array $transaction, array $history): array
    {
        $score = 0;
        $reasons = [];

        // Check for round amounts (potential testing)
        $amount = floatval($transaction['amount']);
        if ($amount >= 100000 && $amount % 100000 == 0) {
            $score += 25;
            $reasons[] = "Large round amount ({$amount})";
        } elseif ($amount >= 10000 && $amount % 10000 == 0) {
            $score += 15;
            $reasons[] = "Round amount ({$amount})";
        }

        // Check for sequential amounts (potential testing pattern)
        $recentAmounts = array_slice(array_column($history, 'amount'), -5);
        if ($this->areAmountsSequential($recentAmounts, $amount)) {
            $score += 30;
            $reasons[] = "Sequential amount pattern detected";
        }

        // Check if same amount repeated many times recently
        $sameAmountCount = count(array_filter($recentAmounts, function($a) use ($amount) {
            return abs($a - $amount) < 1; // Allow small rounding differences
        }));

        if ($sameAmountCount >= 3) {
            $score += 20;
            $reasons[] = "Same amount repeated {$sameAmountCount} times recently";
        }

        // Check for rapid escalation (each transaction much larger than previous)
        if (count($recentAmounts) >= 3) {
            $isEscalating = true;
            for ($i = 1; $i < count($recentAmounts); $i++) {
                if ($recentAmounts[$i] <= $recentAmounts[$i-1] * 1.5) {
                    $isEscalating = false;
                    break;
                }
            }

            if ($isEscalating && $amount > end($recentAmounts) * 1.5) {
                $score += 35;
                $reasons[] = "Rapid amount escalation pattern";
            }
        }

        $isAnomaly = $score >= 30;

        return [
            'is_anomaly' => $isAnomaly,
            'score' => min($score, 100),
            'reason' => implode('; ', $reasons) ?: null,
        ];
    }

    /**
     * Check if amounts are sequential
     */
    protected function areAmountsSequential(array $amounts, float $currentAmount): bool
    {
        if (count($amounts) < 3) {
            return false;
        }

        // Add current amount
        $amounts[] = $currentAmount;

        // Check if differences between consecutive amounts are roughly equal
        $diffs = [];
        for ($i = 1; $i < count($amounts); $i++) {
            $diffs[] = $amounts[$i] - $amounts[$i-1];
        }

        // Calculate standard deviation of differences
        $meanDiff = array_sum($diffs) / count($diffs);
        $variance = 0;
        foreach ($diffs as $diff) {
            $variance += pow($diff - $meanDiff, 2);
        }
        $stdDev = sqrt($variance / count($diffs));

        // Sequential if differences are consistent (low std dev) and non-zero
        return $stdDev < 1000 && abs($meanDiff) > 100;
    }

    /**
     * Calculate standard deviation
     */
    protected function standardDeviation(array $values, float $mean): float
    {
        if (count($values) < 2) {
            return 0;
        }

        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        return sqrt($variance / count($values));
    }

    /**
     * Get account transaction history
     */
    protected function getAccountHistory(string $accountNumber, string $serviceType = 'all'): array
    {
        $query = DB::table('api_transactions')
            ->where('source_account', $accountNumber)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(90));

        if ($serviceType !== 'all') {
            $query->where('service_type', $serviceType);
        }

        return $query
            ->select('amount', 'created_at', 'service_type')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function($row) {
                return [
                    'amount' => floatval($row->amount),
                    'created_at' => $row->created_at,
                    'service_type' => $row->service_type,
                ];
            })
            ->toArray();
    }

    /**
     * Get confidence level based on data quantity and score
     */
    protected function getConfidence(int $historyCount, int $score): string
    {
        if ($historyCount < 20) {
            return 'low';
        }

        if ($historyCount < 50) {
            return $score > 70 ? 'medium' : 'low';
        }

        if ($score > 80) {
            return 'high';
        }

        if ($score > 60) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get anomaly statistics for an account
     */
    public function getAccountAnomalyStats(string $accountNumber): array
    {
        $history = $this->getAccountHistory($accountNumber);

        if (count($history) < 10) {
            return [
                'sufficient_data' => false,
                'transaction_count' => count($history),
                'message' => 'Need at least 10 transactions for analysis',
            ];
        }

        $amounts = array_column($history, 'amount');
        $mean = array_sum($amounts) / count($amounts);
        $stdDev = $this->standardDeviation($amounts, $mean);

        return [
            'sufficient_data' => true,
            'transaction_count' => count($history),
            'mean_amount' => round($mean, 2),
            'std_dev' => round($stdDev, 2),
            'min_amount' => min($amounts),
            'max_amount' => max($amounts),
            'median_amount' => $this->median($amounts),
            'typical_range' => [
                'lower' => round($mean - (2 * $stdDev), 2),
                'upper' => round($mean + (2 * $stdDev), 2),
            ],
        ];
    }

    /**
     * Calculate median
     */
    protected function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }
}
