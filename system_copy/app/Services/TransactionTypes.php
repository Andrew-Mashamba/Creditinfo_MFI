<?php

namespace App\Services;

class TransactionTypes {
    // Asset Transactions
    const ASSET_PURCHASE = 'asset_purchase';
    const ASSET_SALE = 'asset_sale';
    const ASSET_DEPRECIATION = 'asset_depreciation';
    const ASSET_IMPAIRMENT = 'asset_impairment';
    const ASSET_REVALUATION = 'asset_revaluation';
    
    // Revenue Transactions
    const REVENUE_RECOGNITION = 'revenue_recognition';
    const REVENUE_ADJUSTMENT = 'revenue_adjustment';
    const REVENUE_REFUND = 'revenue_refund';
    
    // Expense Transactions
    const EXPENSE_ACCRUAL = 'expense_accrual';
    const EXPENSE_PAYMENT = 'expense_payment';
    const EXPENSE_PREPAYMENT = 'expense_prepayment';
    
    // Liability Transactions
    const LIABILITY_RECOGNITION = 'liability_recognition';
    const LIABILITY_SETTLEMENT = 'liability_settlement';
    const LIABILITY_ADJUSTMENT = 'liability_adjustment';
    
    // Capital Transactions
    const CAPITAL_CONTRIBUTION = 'capital_contribution';
    const CAPITAL_WITHDRAWAL = 'capital_withdrawal';
    const CAPITAL_ADJUSTMENT = 'capital_adjustment';

    public static function getAllTypes() {
        return [
            'asset' => [
                self::ASSET_PURCHASE,
                self::ASSET_SALE,
                self::ASSET_DEPRECIATION,
                self::ASSET_IMPAIRMENT,
                self::ASSET_REVALUATION
            ],
            'revenue' => [
                self::REVENUE_RECOGNITION,
                self::REVENUE_ADJUSTMENT,
                self::REVENUE_REFUND
            ],
            'expense' => [
                self::EXPENSE_ACCRUAL,
                self::EXPENSE_PAYMENT,
                self::EXPENSE_PREPAYMENT
            ],
            'liability' => [
                self::LIABILITY_RECOGNITION,
                self::LIABILITY_SETTLEMENT,
                self::LIABILITY_ADJUSTMENT
            ],
            'capital' => [
                self::CAPITAL_CONTRIBUTION,
                self::CAPITAL_WITHDRAWAL,
                self::CAPITAL_ADJUSTMENT
            ]
        ];
    }
} 