<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialStatementSnapshot extends Model
{
    protected $fillable = [
        'financial_period_id',
        'statement_type',
        'data',
        'version',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'data' => 'json',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the financial period this snapshot belongs to
     */
    public function financialPeriod(): BelongsTo
    {
        return $this->belongsTo(FinancialPeriod::class);
    }

    /**
     * Get the user who created this snapshot
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who approved this snapshot
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Approve the snapshot
     */
    public function approve($userId = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now()
        ]);
    }

    /**
     * Publish the snapshot
     */
    public function publish()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Snapshot must be approved before publishing');
        }
        
        $this->update(['status' => 'published']);
    }

    /**
     * Get the latest snapshot for a statement type
     */
    public static function getLatest($periodId, $statementType)
    {
        return self::where('financial_period_id', $periodId)
            ->where('statement_type', $statementType)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Create a new version of the snapshot
     */
    public function createNewVersion($data)
    {
        $currentVersion = floatval($this->version);
        $newVersion = number_format($currentVersion + 0.1, 1);
        
        return self::create([
            'financial_period_id' => $this->financial_period_id,
            'statement_type' => $this->statement_type,
            'data' => $data,
            'version' => $newVersion,
            'status' => 'draft',
            'created_by' => auth()->id()
        ]);
    }
}