<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $table = 'receivables';

    protected $fillable = [
        'account_number',
        'customer_name',
        'invoice_number',
        'amount',
        'due_date',
        'status',
        'description',
        'receivable_type',
        'service_type',
        'property_type',
        'investment_type',
        'insurance_claim_type',
        'government_agency',
        'contract_type',
        'subscription_type',
        'installment_plan',
        'royalty_type',
        'commission_type',
        'utility_type',
        'healthcare_type',
        'education_type',
        'payment_terms',
        'collection_status',
        'collection_notes',
        'assigned_to',
        'revenue_category',
        'cost_center',
        'project_code',
        'department',
        'document_reference',
        'approval_status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_number', 'account_number');
    }
} 