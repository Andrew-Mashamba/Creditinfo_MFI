<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProvisionSettings extends Model
{
    use HasFactory;

    protected $table = 'loan_provision_settings';

    protected $fillable = [
        'stage1_days',
        'stage2_days',
        'stage3_days',
        'stage1_rate',
        'stage2_rate',
        'stage3_rate',
        'performing_rate',
        'watch_rate',
        'substandard_rate',
        'doubtful_rate',
        'loss_rate',
        'default_pd',
        'default_lgd',
        'optimistic_adjustment',
        'base_adjustment',
        'pessimistic_adjustment',
        'sicr_threshold',
        'npl_threshold',
        'enable_forward_looking',
        'enable_collateral_adjustment',
        'enable_guarantor_adjustment',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'stage1_days' => 'integer',
        'stage2_days' => 'integer',
        'stage3_days' => 'integer',
        'stage1_rate' => 'decimal:2',
        'stage2_rate' => 'decimal:2',
        'stage3_rate' => 'decimal:2',
        'performing_rate' => 'decimal:2',
        'watch_rate' => 'decimal:2',
        'substandard_rate' => 'decimal:2',
        'doubtful_rate' => 'decimal:2',
        'loss_rate' => 'decimal:2',
        'default_pd' => 'decimal:4',
        'default_lgd' => 'decimal:4',
        'optimistic_adjustment' => 'decimal:2',
        'base_adjustment' => 'decimal:2',
        'pessimistic_adjustment' => 'decimal:2',
        'sicr_threshold' => 'decimal:2',
        'npl_threshold' => 'decimal:2',
        'enable_forward_looking' => 'boolean',
        'enable_collateral_adjustment' => 'boolean',
        'enable_guarantor_adjustment' => 'boolean',
    ];

    /**
     * Get the provision rate for a specific classification
     */
    public function getRateForClassification($classification)
    {
        $rates = [
            'PERFORMING' => $this->performing_rate ?? 1.0,
            'WATCH' => $this->watch_rate ?? 5.0,
            'SUBSTANDARD' => $this->substandard_rate ?? 25.0,
            'DOUBTFUL' => $this->doubtful_rate ?? 50.0,
            'LOSS' => $this->loss_rate ?? 100.0,
        ];

        return $rates[strtoupper($classification)] ?? 0;
    }

    /**
     * Get the ECL stage based on days in arrears
     */
    public function getStageByDays($daysInArrears)
    {
        if ($daysInArrears <= ($this->stage1_days ?? 30)) {
            return 1;
        } elseif ($daysInArrears <= ($this->stage2_days ?? 90)) {
            return 2;
        } else {
            return 3;
        }
    }

    /**
     * Get the provision rate for a specific stage
     */
    public function getRateForStage($stage)
    {
        switch ($stage) {
            case 1:
                return $this->stage1_rate ?? 1.0;
            case 2:
                return $this->stage2_rate ?? 10.0;
            case 3:
                return $this->stage3_rate ?? 100.0;
            default:
                return 0;
        }
    }

    /**
     * Get economic scenario adjustment
     */
    public function getScenarioAdjustment($scenario)
    {
        switch (strtolower($scenario)) {
            case 'optimistic':
                return $this->optimistic_adjustment ?? -20.0;
            case 'pessimistic':
                return $this->pessimistic_adjustment ?? 30.0;
            case 'base':
            default:
                return $this->base_adjustment ?? 0.0;
        }
    }

    /**
     * Initialize default settings if none exist
     */
    public static function initializeDefaults()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'stage1_days' => 30,
                'stage2_days' => 90,
                'stage3_days' => 180,
                'stage1_rate' => 1.0,
                'stage2_rate' => 10.0,
                'stage3_rate' => 100.0,
                'performing_rate' => 1.0,
                'watch_rate' => 5.0,
                'substandard_rate' => 25.0,
                'doubtful_rate' => 50.0,
                'loss_rate' => 100.0,
                'default_pd' => 0.05,
                'default_lgd' => 0.45,
                'optimistic_adjustment' => -20.0,
                'base_adjustment' => 0.0,
                'pessimistic_adjustment' => 30.0,
                'sicr_threshold' => 2.0,
                'npl_threshold' => 90,
                'enable_forward_looking' => true,
                'enable_collateral_adjustment' => true,
                'enable_guarantor_adjustment' => true,
            ]
        );
    }
}