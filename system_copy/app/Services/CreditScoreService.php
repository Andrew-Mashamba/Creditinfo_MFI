<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditScoreService
{
    /**
     * Get credit score for a specific client
     */
    public function getClientCreditScore($clientId)
    {
        try {
            $score = DB::table('scores')
                ->where('client_id', $clientId)
                ->orderBy('date', 'desc')
                ->first();

            if (!$score) {
                return $this->getDefaultScore();
            }

            return [
                'score' => $score->score,
                'grade' => $score->grade,
                'trend' => $score->trend,
                'probability_of_default' => $score->probability_of_default,
                'reasons' => json_decode($score->reasons, true),
                'date' => $score->date,
                'risk_level' => $this->getRiskLevel($score->score),
                'risk_color' => $this->getRiskColor($score->score),
                'risk_description' => $this->getRiskDescription($score->score)
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching credit score: ' . $e->getMessage());
            return $this->getDefaultScore();
        }
    }

    /**
     * Get risk level based on score
     */
    public function getRiskLevel($score)
    {
        if ($score >= 713) return 'A';
        if ($score >= 677) return 'B';
        if ($score >= 641) return 'C';
        if ($score >= 574) return 'D';
        return 'E';
    }

    /**
     * Get risk color for UI
     */
    public function getRiskColor($score)
    {
        if ($score >= 713) return '#00FF00'; // Green - Very Low Risk
        if ($score >= 677) return '#00FF7F'; // Light Green - Low Risk
        if ($score >= 641) return '#FFFF00'; // Yellow - Average Risk
        if ($score >= 574) return '#FFA500'; // Orange - High Risk
        return '#FF0000'; // Red - Very High Risk
    }

    /**
     * Get risk description
     */
    public function getRiskDescription($score)
    {
        if ($score >= 713) return 'Very Low Risk';
        if ($score >= 677) return 'Low Risk';
        if ($score >= 641) return 'Average Risk';
        if ($score >= 574) return 'High Risk';
        return 'Very High Risk';
    }

    /**
     * Get default score when no data is available
     */
    private function getDefaultScore()
    {
        return [
            'score' => 500,
            'grade' => 'E',
            'trend' => 'Stable',
            'probability_of_default' => 'High',
            'reasons' => ['No credit history available'],
            'date' => now(),
            'risk_level' => 'E',
            'risk_color' => '#FF0000',
            'risk_description' => 'Very High Risk - No Data'
        ];
    }

    /**
     * Check if credit score meets product requirements
     */
    public function meetsProductRequirements($clientId, $productScoreLimit)
    {
        $creditScore = $this->getClientCreditScore($clientId);
        return $creditScore['score'] >= $productScoreLimit;
    }

    /**
     * Get credit score trend analysis
     */
    public function getTrendAnalysis($clientId)
    {
        try {
            $scores = DB::table('scores')
                ->where('client_id', $clientId)
                ->orderBy('date', 'desc')
                ->limit(3)
                ->get();

            if ($scores->count() < 2) {
                return 'Insufficient data for trend analysis';
            }

            $trends = $scores->pluck('trend')->toArray();
            $latestTrend = $trends[0];

            return [
                'current_trend' => $latestTrend,
                'trend_direction' => $this->getTrendDirection($trends),
                'recommendation' => $this->getTrendRecommendation($latestTrend)
            ];
        } catch (\Exception $e) {
            Log::error('Error analyzing credit score trend: ' . $e->getMessage());
            return 'Error analyzing trend';
        }
    }

    /**
     * Get trend direction
     */
    private function getTrendDirection($trends)
    {
        if (count($trends) < 2) return 'Stable';
        
        $improving = 0;
        $declining = 0;
        
        foreach ($trends as $trend) {
            if (in_array($trend, ['Improving', 'Strongly Improving'])) $improving++;
            if (in_array($trend, ['Declining', 'Strongly Declining'])) $declining++;
        }
        
        if ($improving > $declining) return 'Improving';
        if ($declining > $improving) return 'Declining';
        return 'Stable';
    }

    /**
     * Get trend recommendation
     */
    private function getTrendRecommendation($trend)
    {
        switch ($trend) {
            case 'Strongly Improving':
                return 'Excellent credit behavior. Consider premium products.';
            case 'Improving':
                return 'Good credit behavior. Standard products recommended.';
            case 'Stable':
                return 'Stable credit profile. Monitor for improvements.';
            case 'Declining':
                return 'Credit behavior declining. Requires close monitoring.';
            case 'Strongly Declining':
                return 'Serious credit concerns. High risk assessment required.';
            default:
                return 'Insufficient data for recommendation.';
        }
    }
} 