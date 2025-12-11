<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialStatementNote extends Model
{
    protected $fillable = [
        'financial_period_id',
        'note_number',
        'note_title',
        'note_type',
        'content',
        'related_accounts',
        'breakdown_data',
        'is_mandatory',
        'display_order'
    ];

    protected $casts = [
        'related_accounts' => 'json',
        'breakdown_data' => 'json',
        'is_mandatory' => 'boolean'
    ];

    /**
     * Get the financial period this note belongs to
     */
    public function financialPeriod(): BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    /**
     * Get notes for specific accounts
     */
    public function scopeForAccounts($query, array $accountNumbers)
    {
        return $query->where(function($q) use ($accountNumbers) {
            foreach ($accountNumbers as $accountNumber) {
                $q->orWhereJsonContains('related_accounts', $accountNumber);
            }
        });
    }

    /**
     * Get notes by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('note_type', $type);
    }

    /**
     * Get mandatory notes
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Get the formatted content with any placeholders replaced
     */
    public function getFormattedContentAttribute()
    {
        $content = $this->content;
        
        // Replace placeholders with actual values
        if ($this->breakdown_data) {
            foreach ($this->breakdown_data as $key => $value) {
                if (is_numeric($value)) {
                    $value = number_format($value, 2);
                }
                $content = str_replace("{{{$key}}}", $value, $content);
            }
        }
        
        return $content;
    }

    /**
     * Generate standard notes for a period
     */
    public static function generateStandardNotes($periodId)
    {
        $notes = [];
        
        // Note 1: Basis of Preparation
        $notes[] = [
            'financial_period_id' => $periodId,
            'note_number' => 1,
            'note_title' => 'Basis of Preparation',
            'note_type' => 'accounting_policy',
            'content' => 'These financial statements have been prepared in accordance with International Financial Reporting Standards (IFRS) and the requirements of the Cooperative Societies Act.',
            'is_mandatory' => true,
            'display_order' => 1
        ];
        
        // Note 2: Summary of Significant Accounting Policies
        $notes[] = [
            'financial_period_id' => $periodId,
            'note_number' => 2,
            'note_title' => 'Summary of Significant Accounting Policies',
            'note_type' => 'accounting_policy',
            'content' => 'The principal accounting policies applied in the preparation of these financial statements are set out below. These policies have been consistently applied to all years presented.',
            'is_mandatory' => true,
            'display_order' => 2
        ];
        
        // Note 3: Critical Accounting Estimates and Judgments
        $notes[] = [
            'financial_period_id' => $periodId,
            'note_number' => 3,
            'note_title' => 'Critical Accounting Estimates and Judgments',
            'note_type' => 'disclosure',
            'content' => 'The preparation of financial statements requires management to make judgments, estimates and assumptions that affect the reported amounts. Actual results may differ from these estimates.',
            'is_mandatory' => true,
            'display_order' => 3
        ];
        
        foreach ($notes as $note) {
            self::create($note);
        }
    }
}