<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatementRelationship extends Model
{
    protected $fillable = [
        'financial_period_id',
        'source_statement',
        'source_item',
        'target_statement',
        'target_item',
        'relationship_type',
        'description',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    /**
     * Get the financial period this relationship belongs to
     */
    public function financialPeriod(): BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    /**
     * Get relationships for a specific statement
     */
    public function scopeForStatement($query, $statementType)
    {
        return $query->where(function($q) use ($statementType) {
            $q->where('source_statement', $statementType)
              ->orWhere('target_statement', $statementType);
        });
    }

    /**
     * Get all flows from one statement to another
     */
    public static function getFlows($periodId, $fromStatement, $toStatement)
    {
        return self::where('financial_period_id', $periodId)
            ->where('source_statement', $fromStatement)
            ->where('target_statement', $toStatement)
            ->where('relationship_type', 'flows_to')
            ->get();
    }

    /**
     * Verify relationship integrity
     */
    public function verifyIntegrity()
    {
        // For 'equals' relationships, amounts should match
        if ($this->relationship_type === 'equals') {
            $sourceAmount = $this->getSourceAmount();
            $targetAmount = $this->getTargetAmount();
            
            return abs($sourceAmount - $targetAmount) < 0.01;
        }
        
        return true;
    }

    /**
     * Get the source amount from the financial statements
     */
    private function getSourceAmount()
    {
        return FinancialStatementItem::where('financial_period_id', $this->financial_period_id)
            ->where('statement_type', $this->source_statement)
            ->where('account_number', $this->source_item)
            ->value('amount') ?? 0;
    }

    /**
     * Get the target amount from the financial statements
     */
    private function getTargetAmount()
    {
        return FinancialStatementItem::where('financial_period_id', $this->financial_period_id)
            ->where('statement_type', $this->target_statement)
            ->where('account_number', $this->target_item)
            ->value('amount') ?? 0;
    }
}