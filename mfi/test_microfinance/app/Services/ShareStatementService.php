<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ShareStatementService
{
    /**
     * Generate share statement for a member
     *
     * @param string $clientNumber Member's client number
     * @param int|null $productId Share product ID (optional)
     * @param Carbon|null $startDate Start date for the period
     * @param Carbon|null $endDate End date for the period
     * @return array
     */
    public function generateStatement($clientNumber, $productId = null, $startDate = null, $endDate = null)
    {
        try {
            // Default date range: current year
            $startDate = $startDate ?? Carbon::now()->startOfYear();
            $endDate = $endDate ?? Carbon::now();

            Log::info('Generating share statement', [
                'client_number' => $clientNumber,
                'product_id' => $productId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);

            // Get member details
            $member = DB::table('clients')
                ->where('client_number', $clientNumber)
                ->first();

            if (!$member) {
                throw new Exception('Member not found');
            }

            // Get share registers for the member
            $shareRegistersQuery = DB::table('share_registers')
                ->where('member_number', $clientNumber);

            if ($productId) {
                $shareRegistersQuery->where('product_id', $productId);
            }

            $shareRegisters = $shareRegistersQuery->get();

            $statementData = [];

            foreach ($shareRegisters as $register) {
                // Get opening balance (shares held before start date)
                $openingBalance = $this->getOpeningBalance($register, $startDate);

                // Get all transactions in the period
                $transactions = $this->getTransactions($register, $startDate, $endDate);

                // Calculate totals
                $totalIssued = $transactions->where('transaction_type', 'ISSUE')->sum('shares');
                $totalRedeemed = $transactions->where('transaction_type', 'REDEMPTION')->sum('shares');
                $closingBalance = $openingBalance + $totalIssued - $totalRedeemed;

                // Get product details
                $product = DB::table('sub_products')
                    ->where('id', $register->product_id)
                    ->first();

                $statementData[] = [
                    'register_id' => $register->id,
                    'product_id' => $register->product_id,
                    'product_name' => $register->product_name,
                    'share_account_number' => $register->share_account_number,
                    'nominal_price' => $register->nominal_price,
                    'opening_balance' => $openingBalance,
                    'opening_value' => $openingBalance * $register->nominal_price,
                    'transactions' => $transactions,
                    'total_issued' => $totalIssued,
                    'total_redeemed' => $totalRedeemed,
                    'closing_balance' => $closingBalance,
                    'closing_value' => $closingBalance * $register->nominal_price,
                    'net_change' => $totalIssued - $totalRedeemed,
                    'current_price' => $register->current_price ?? $register->nominal_price,
                    'market_value' => $closingBalance * ($register->current_price ?? $register->nominal_price)
                ];
            }

            // Calculate grand totals
            $grandTotals = [
                'opening_shares' => array_sum(array_column($statementData, 'opening_balance')),
                'opening_value' => array_sum(array_column($statementData, 'opening_value')),
                'total_issued' => array_sum(array_column($statementData, 'total_issued')),
                'total_redeemed' => array_sum(array_column($statementData, 'total_redeemed')),
                'closing_shares' => array_sum(array_column($statementData, 'closing_balance')),
                'closing_value' => array_sum(array_column($statementData, 'closing_value')),
                'market_value' => array_sum(array_column($statementData, 'market_value'))
            ];

            return [
                'status' => 'success',
                'member' => [
                    'client_number' => $member->client_number,
                    'first_name' => $member->first_name,
                    'middle_name' => $member->middle_name ?? '',
                    'last_name' => $member->last_name,
                    'full_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name)
                ],
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'start_date_formatted' => $startDate->format('d M Y'),
                    'end_date_formatted' => $endDate->format('d M Y')
                ],
                'statements' => $statementData,
                'grand_totals' => $grandTotals,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            Log::error('Error generating share statement', [
                'client_number' => $clientNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get opening balance (shares held before start date)
     *
     * @param object $register
     * @param Carbon $startDate
     * @return int
     */
    protected function getOpeningBalance($register, $startDate)
    {
        try {
            // Get all issuances before start date
            $issuedBefore = DB::table('issued_shares')
                ->where('client_number', $register->member_number)
                ->where('product', $register->product_id)
                ->where('status', 'COMPLETED')
                ->whereDate('created_at', '<', $startDate->format('Y-m-d'))
                ->sum('number_of_shares');

            // Get all redemptions before start date
            $redeemedBefore = DB::table('share_redemptions')
                ->where('client_number', $register->member_number)
                ->where('product', $register->product_id)
                ->where('status', 'COMPLETED')
                ->whereDate('created_at', '<', $startDate->format('Y-m-d'))
                ->sum('number_of_shares');

            return max(0, $issuedBefore - $redeemedBefore);

        } catch (Exception $e) {
            Log::error('Error calculating opening balance', [
                'register_id' => $register->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get all transactions in the period
     *
     * @param object $register
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    protected function getTransactions($register, $startDate, $endDate)
    {
        $transactions = collect();

        try {
            // Get issuances in the period
            $issuances = DB::table('issued_shares')
                ->where('client_number', $register->member_number)
                ->where('product', $register->product_id)
                ->where('status', 'COMPLETED')
                ->whereBetween('created_at', [
                    $startDate->format('Y-m-d') . ' 00:00:00',
                    $endDate->format('Y-m-d') . ' 23:59:59'
                ])
                ->select(
                    'id',
                    'reference_number',
                    'created_at as transaction_date',
                    'number_of_shares as shares',
                    'nominal_price',
                    'total_value',
                    DB::raw("'ISSUE' as transaction_type"),
                    DB::raw("'Share Purchase' as description")
                )
                ->get();

            // Get redemptions in the period
            $redemptions = DB::table('share_redemptions')
                ->where('client_number', $register->member_number)
                ->where('product', $register->product_id)
                ->where('status', 'COMPLETED')
                ->whereBetween('created_at', [
                    $startDate->format('Y-m-d') . ' 00:00:00',
                    $endDate->format('Y-m-d') . ' 23:59:59'
                ])
                ->select(
                    'id',
                    'reference_number',
                    'created_at as transaction_date',
                    'number_of_shares as shares',
                    'nominal_price',
                    'total_value',
                    DB::raw("'REDEMPTION' as transaction_type"),
                    DB::raw("'Share Redemption' as description")
                )
                ->get();

            // Merge and sort by date
            $transactions = $issuances->merge($redemptions)->sortBy('transaction_date');

        } catch (Exception $e) {
            Log::error('Error fetching transactions', [
                'register_id' => $register->id,
                'error' => $e->getMessage()
            ]);
        }

        return $transactions;
    }

    /**
     * Get statement summary for all members
     *
     * @param int|null $productId
     * @param Carbon|null $asOfDate
     * @return array
     */
    public function getStatementSummary($productId = null, $asOfDate = null)
    {
        try {
            $asOfDate = $asOfDate ?? Carbon::now();

            $query = DB::table('share_registers')
                ->join('clients', 'share_registers.member_number', '=', 'clients.client_number')
                ->join('sub_products', 'share_registers.product_id', '=', 'sub_products.id')
                ->where('share_registers.status', 'ACTIVE')
                ->select(
                    'share_registers.*',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'sub_products.product_name',
                    'sub_products.nominal_price'
                );

            if ($productId) {
                $query->where('share_registers.product_id', $productId);
            }

            $registers = $query->get();

            $summary = [
                'total_members' => $registers->unique('member_number')->count(),
                'total_shares' => $registers->sum('current_share_balance'),
                'total_value' => $registers->sum('total_share_value'),
                'by_product' => []
            ];

            // Group by product
            $byProduct = $registers->groupBy('product_id');
            foreach ($byProduct as $productId => $productRegisters) {
                $summary['by_product'][] = [
                    'product_id' => $productId,
                    'product_name' => $productRegisters->first()->product_name,
                    'members_count' => $productRegisters->count(),
                    'total_shares' => $productRegisters->sum('current_share_balance'),
                    'total_value' => $productRegisters->sum('total_share_value')
                ];
            }

            return [
                'status' => 'success',
                'as_of_date' => $asOfDate->format('Y-m-d'),
                'summary' => $summary
            ];

        } catch (Exception $e) {
            Log::error('Error generating statement summary', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get members with shares
     *
     * @param string $searchTerm
     * @return \Illuminate\Support\Collection
     */
    public function searchMembers($searchTerm = '')
    {
        try {
            $query = DB::table('share_registers')
                ->join('clients', 'share_registers.member_number', '=', 'clients.client_number')
                ->where('share_registers.status', 'ACTIVE')
                ->where('share_registers.current_share_balance', '>', 0)
                ->select(
                    'clients.client_number',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    DB::raw("CONCAT(clients.first_name, ' ', COALESCE(clients.middle_name, ''), ' ', clients.last_name) as full_name"),
                    DB::raw("COUNT(share_registers.id) as share_accounts_count"),
                    DB::raw("SUM(share_registers.current_share_balance) as total_shares")
                )
                ->groupBy('clients.client_number', 'clients.first_name', 'clients.middle_name', 'clients.last_name');

            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('clients.client_number', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('clients.first_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('clients.last_name', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            return $query->orderBy('clients.first_name')->get();

        } catch (Exception $e) {
            Log::error('Error searching members', [
                'search_term' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Get certificate data for a member
     *
     * @param string $clientNumber Member's client number
     * @return array
     */
    public function getCertificateData($clientNumber)
    {
        try {
            // Get institution details
            $institution = DB::table('institutions')
                ->where('id', 1)
                ->first();

            // Get member details
            $member = DB::table('clients')
                ->where('client_number', $clientNumber)
                ->first();

            if (!$member) {
                throw new Exception('Member not found');
            }

            // Get share registers for the member
            $shareRegisters = DB::table('share_registers')
                ->where('member_number', $clientNumber)
                ->where('status', 'ACTIVE')
                ->where('current_share_balance', '>', 0)
                ->get();

            if ($shareRegisters->isEmpty()) {
                throw new Exception('No active shares found for this member');
            }

            $certificateData = [];
            $totalShares = 0;
            $totalValue = 0;

            foreach ($shareRegisters as $register) {
                // Get product details
                $product = DB::table('sub_products')
                    ->where('id', $register->product_id)
                    ->first();

                $certificateData[] = [
                    'product_name' => $product->product_name ?? $register->product_name,
                    'share_account_number' => $register->share_account_number,
                    'shares' => $register->current_share_balance,
                    'nominal_price' => $register->nominal_price,
                    'value' => $register->current_share_balance * $register->nominal_price,
                    'certificate_number' => 'CERT-' . $register->share_account_number . '-' . date('Ymd')
                ];

                $totalShares += $register->current_share_balance;
                $totalValue += $register->current_share_balance * $register->nominal_price;
            }

            return [
                'status' => 'success',
                'institution' => [
                    'name' => $institution->institution_name ?? $institution->name ?? 'SACCO',
                    'address' => $institution->address ?? '',
                    'phone' => $institution->phone_number ?? $institution->contact_phone ?? '',
                    'email' => $institution->email ?? $institution->contact_email ?? ''
                ],
                'member' => [
                    'client_number' => $member->client_number,
                    'first_name' => $member->first_name,
                    'middle_name' => $member->middle_name ?? '',
                    'last_name' => $member->last_name,
                    'full_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name)
                ],
                'shares' => $certificateData,
                'total_shares' => $totalShares,
                'total_value' => $totalValue,
                'issue_date' => now()->format('d M Y'),
                'certificate_id' => 'SHARE-CERT-' . $clientNumber . '-' . now()->format('YmdHis')
            ];

        } catch (Exception $e) {
            Log::error('Error getting certificate data', [
                'client_number' => $clientNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate consolidated statement for all members
     *
     * @param int|null $productId Share product ID (optional)
     * @param Carbon|null $startDate Start date for the period
     * @param Carbon|null $endDate End date for the period
     * @return array
     */
    public function generateConsolidatedStatement($productId = null, $startDate = null, $endDate = null)
    {
        try {
            // Default date range: current year
            $startDate = $startDate ?? Carbon::now()->startOfYear();
            $endDate = $endDate ?? Carbon::now();

            Log::info('Generating consolidated share statement', [
                'product_id' => $productId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);

            // Get all members with active shares
            $members = $this->searchMembers('');

            if ($members->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => 'No members with active shares found'
                ];
            }

            $consolidatedData = [];
            $grandTotals = [
                'opening_shares' => 0,
                'opening_value' => 0,
                'total_issued' => 0,
                'total_redeemed' => 0,
                'closing_shares' => 0,
                'closing_value' => 0,
                'market_value' => 0
            ];

            foreach ($members as $member) {
                // Get share registers for the member
                $shareRegistersQuery = DB::table('share_registers')
                    ->where('member_number', $member->client_number)
                    ->where('status', 'ACTIVE');

                if ($productId) {
                    $shareRegistersQuery->where('product_id', $productId);
                }

                $shareRegisters = $shareRegistersQuery->get();

                $memberOpeningBalance = 0;
                $memberOpeningValue = 0;
                $memberTotalIssued = 0;
                $memberTotalRedeemed = 0;
                $memberClosingBalance = 0;
                $memberClosingValue = 0;
                $memberMarketValue = 0;

                foreach ($shareRegisters as $register) {
                    // Get opening balance
                    $openingBalance = $this->getOpeningBalance($register, $startDate);

                    // Get transactions in the period
                    $transactions = $this->getTransactions($register, $startDate, $endDate);

                    // Calculate totals for this register
                    $totalIssued = $transactions->where('transaction_type', 'ISSUE')->sum('shares');
                    $totalRedeemed = $transactions->where('transaction_type', 'REDEMPTION')->sum('shares');
                    $closingBalance = $openingBalance + $totalIssued - $totalRedeemed;

                    $memberOpeningBalance += $openingBalance;
                    $memberOpeningValue += $openingBalance * $register->nominal_price;
                    $memberTotalIssued += $totalIssued;
                    $memberTotalRedeemed += $totalRedeemed;
                    $memberClosingBalance += $closingBalance;
                    $memberClosingValue += $closingBalance * $register->nominal_price;
                    $memberMarketValue += $closingBalance * ($register->current_price ?? $register->nominal_price);
                }

                $consolidatedData[] = [
                    'client_number' => $member->client_number,
                    'full_name' => $member->full_name,
                    'opening_balance' => $memberOpeningBalance,
                    'opening_value' => $memberOpeningValue,
                    'total_issued' => $memberTotalIssued,
                    'total_redeemed' => $memberTotalRedeemed,
                    'closing_balance' => $memberClosingBalance,
                    'closing_value' => $memberClosingValue,
                    'net_change' => $memberTotalIssued - $memberTotalRedeemed,
                    'market_value' => $memberMarketValue
                ];

                // Add to grand totals
                $grandTotals['opening_shares'] += $memberOpeningBalance;
                $grandTotals['opening_value'] += $memberOpeningValue;
                $grandTotals['total_issued'] += $memberTotalIssued;
                $grandTotals['total_redeemed'] += $memberTotalRedeemed;
                $grandTotals['closing_shares'] += $memberClosingBalance;
                $grandTotals['closing_value'] += $memberClosingValue;
                $grandTotals['market_value'] += $memberMarketValue;
            }

            return [
                'status' => 'success',
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'start_date_formatted' => $startDate->format('d M Y'),
                    'end_date_formatted' => $endDate->format('d M Y')
                ],
                'members' => $consolidatedData,
                'grand_totals' => $grandTotals,
                'total_members' => count($consolidatedData),
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            Log::error('Error generating consolidated statement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
