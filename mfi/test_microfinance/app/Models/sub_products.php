<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sub_products extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_type',
        'product_id',
        'savings_type_id',
        'default_status',
        'sub_product_name',
        'sub_product_id',
        'deposit_type_id',
        'share_type_id',
        'sub_product_status',
        'currency',
        'deposit',
        'deposit_charge',
        'min_balance',
        'created_by',
        'updated_by',
        'deposit_charge_min_value',
        'deposit_charge_max_value',
        'withdraw',
        'withdraw_charge',
        'withdraw_charge_min_value',
        'withdraw_charge_max_value',
        'interest_value',
        'interest_tenure',
        'maintenance_fees',
        'maintenance_fees_value',
        'profit_account',
        'inactivity',
        'create_during_registration',
        'activated_by_lower_limit',
        'requires_approval',
        'generate_atm_card_profile',
        'allow_statement_generation',
        'send_notifications',
        'require_image_member',
        'require_image_id',
        'require_mobile_number',
        'generate_mobile_profile',
        'notes',
        'interest',
        'ledger_fees',
        'ledger_fees_value',
        'total_shares',
        'shares_per_member',
        'nominal_price',
        'shares_allocated',
        'available_shares',
        'institution_id',
        'branch',
        'category_code',
        'sub_category_code',
        'major_category_code',
        'status',
        'collection_account_withdraw_charges',
        'collection_account_deposit_charges',
        'collection_account_interest_charges',
        'product_account',
        // New Share Settings
        'minimum_required_shares',
        'lock_in_period',
        'dividend_eligibility_period',
        'dividend_payment_frequency',
        'payment_methods',
        'withdrawal_approval_level',
        'allow_share_transfer',
        'allow_share_withdrawal',
        'enable_dividend_calculation',
        'sms_sender_name',
        'sms_api_key',
        'sms_enabled'
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'allow_share_transfer' => 'boolean',
        'allow_share_withdrawal' => 'boolean',
        'enable_dividend_calculation' => 'boolean',
        'sms_enabled' => 'boolean'
    ];

    public function savingsType()
    {
        return $this->belongsTo(SavingsType::class, 'savings_type_id');
    }

    public function depositType()
    {
        return $this->belongsTo(DepositType::class, 'deposit_type_id');
    }
}
