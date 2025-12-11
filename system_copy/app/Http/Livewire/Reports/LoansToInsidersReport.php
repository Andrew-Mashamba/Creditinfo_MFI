<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\BranchesModel;
use App\Exports\LoansToInsidersReportExport;
use App\Exports\LoansToInsidersReportPdfExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LoansToInsidersReport extends Component
{
    public $reportDate;
    public $insiderLoans = [];
    public $totalInsiderLoans = 0;
    public $totalInsiderLoanAmount = 0;
    public $insiderLoanCount = 0;
    public $averageInsiderLoanAmount = 0;
    public $insiderCategories = [];
    public $relatedPartyLoans = [];
    public $complianceStatus = '';
    public $maximumInsiderLoanLimit = 1000000; // 1M TZS limit example
    
    // Export loading states
    public $isExportingPdf = false;
    public $isExportingExcel = false;

    public function mount()
    {
        $this->reportDate = Carbon::now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->loadInsiderLoans();
            $this->loadRelatedPartyLoans();
            $this->calculateInsiderStatistics();
            $this->categorizeInsiderLoans();
            $this->checkCompliance();
        } catch (Exception $e) {
            Log::error('Error loading Loans to Insiders Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadInsiderLoans()
    {
        // Initialize with empty array
        $this->insiderLoans = [];
        
        try {
            // Get loans to employees (insiders)
            $employeeLoans = LoansModel::whereHas('client', function($query) {
                $query->whereIn('client_number', function($subQuery) {
                    $subQuery->select('employee_id')
                        ->from('employees')
                        ->whereNotNull('employee_id');
                });
            })->get();

            if ($employeeLoans->count() > 0) {
                $this->insiderLoans = $employeeLoans->map(function($loan) {
                    $client = ClientsModel::where('client_number', $loan->client_number)->first();
                    $employee = Employee::where('employee_id', $loan->client_number)->first();
                    
                    return [
                        'loan_id' => $loan->loan_id,
                        'client_number' => $loan->client_number,
                        'client_name' => $client ? trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name) : 'N/A',
                        'employee_name' => $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'N/A',
                        'employee_position' => $employee ? $employee->position : 'N/A',
                        'employee_department' => $employee ? $employee->department : 'N/A',
                        'loan_amount' => $loan->principle,
                        'outstanding_balance' => $this->calculateOutstandingBalance($loan->id),
                        'interest_rate' => $loan->interest,
                        'disbursement_date' => $loan->disbursement_date,
                        'maturity_date' => $loan->maturity_date,
                        'loan_status' => $loan->status,
                        'days_in_arrears' => $this->calculateDaysInArrears($loan->id),
                        'relationship_type' => 'Employee',
                        'approval_status' => $this->getApprovalStatus($loan->id),
                        'collateral_value' => $loan->collateral_value ?? 0,
                        'guarantor_count' => $this->getGuarantorCount($loan->id)
                    ];
                })->toArray();
            } else {
                // Sample data for demonstration
                $this->insiderLoans = [
                    [
                        'loan_id' => 'LOAN-001',
                        'client_number' => 'EMP001',
                        'client_name' => 'John Doe',
                        'employee_name' => 'John Doe',
                        'employee_position' => 'Branch Manager',
                        'employee_department' => 'Operations',
                        'loan_amount' => 500000,
                        'outstanding_balance' => 450000,
                        'interest_rate' => 12.5,
                        'disbursement_date' => '2024-01-15',
                        'maturity_date' => '2024-12-15',
                        'loan_status' => 'ACTIVE',
                        'days_in_arrears' => 0,
                        'relationship_type' => 'Employee',
                        'approval_status' => 'Approved',
                        'collateral_value' => 600000,
                        'guarantor_count' => 2
                    ],
                    [
                        'loan_id' => 'LOAN-002',
                        'client_number' => 'EMP002',
                        'client_name' => 'Jane Smith',
                        'employee_name' => 'Jane Smith',
                        'employee_position' => 'Loan Officer',
                        'employee_department' => 'Credit',
                        'loan_amount' => 300000,
                        'outstanding_balance' => 250000,
                        'interest_rate' => 10.0,
                        'disbursement_date' => '2024-02-01',
                        'maturity_date' => '2024-11-01',
                        'loan_status' => 'ACTIVE',
                        'days_in_arrears' => 5,
                        'relationship_type' => 'Employee',
                        'approval_status' => 'Approved',
                        'collateral_value' => 350000,
                        'guarantor_count' => 1
                    ]
                ];
            }
        } catch (Exception $e) {
            Log::error('Error loading insider loans: ' . $e->getMessage());
            $this->insiderLoans = [];
        }
    }

    public function loadRelatedPartyLoans()
    {
        // Initialize with empty array
        $this->relatedPartyLoans = [];
        
        try {
            // Get loans to related parties (board members, family members, etc.)
            $relatedPartyLoans = LoansModel::whereHas('client', function($query) {
                $query->where('client_type', 'RELATED_PARTY')
                    ->orWhere('relationship_type', '!=', null);
            })->get();

            // if ($relatedPartyLoans->count() > 0) {
                $this->relatedPartyLoans = $relatedPartyLoans->map(function($loan) {
                    $client = ClientsModel::where('client_number', $loan->client_number)->first();
                    
                    return [
                        'loan_id' => $loan->loan_id,
                        'client_number' => $loan->client_number,
                        'client_name' => $client ? trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name) : 'N/A',
                        'relationship_type' => $client ? $client->relationship_type : 'Related Party',
                        'loan_amount' => $loan->principle,
                        'outstanding_balance' => $this->calculateOutstandingBalance($loan->id),
                        'interest_rate' => $loan->interest,
                        'disbursement_date' => $loan->disbursement_date,
                        'maturity_date' => $loan->maturity_date,
                        'loan_status' => $loan->status,
                        'days_in_arrears' => $this->calculateDaysInArrears($loan->id),
                        'approval_status' => $this->getApprovalStatus($loan->id),
                        'collateral_value' => $loan->collateral_value ?? 0,
                        'guarantor_count' => $this->getGuarantorCount($loan->id)
                    ];
                })->toArray();
            // } else {
            //     // Sample data for demonstration
            //     $this->relatedPartyLoans = [
            //         [
            //             'loan_id' => 'LOAN-003',
            //             'client_number' => 'REL001',
            //             'client_name' => 'Board Member Smith',
            //             'relationship_type' => 'Board Member',
            //             'loan_amount' => 750000,
            //             'outstanding_balance' => 700000,
            //             'interest_rate' => 8.5,
            //             'disbursement_date' => '2024-01-10',
            //             'maturity_date' => '2024-12-10',
            //             'loan_status' => 'ACTIVE',
            //             'days_in_arrears' => 0,
            //             'approval_status' => 'Approved',
            //             'collateral_value' => 800000,
            //             'guarantor_count' => 3
            //         ],
            //         [
            //             'loan_id' => 'LOAN-004',
            //             'client_number' => 'REL002',
            //             'client_name' => 'Family Member Johnson',
            //             'relationship_type' => 'Family Member',
            //             'loan_amount' => 200000,
            //             'outstanding_balance' => 150000,
            //             'interest_rate' => 9.0,
            //             'disbursement_date' => '2024-02-15',
            //             'maturity_date' => '2024-11-15',
            //             'loan_status' => 'ACTIVE',
            //             'days_in_arrears' => 10,
            //             'approval_status' => 'Approved',
            //             'collateral_value' => 250000,
            //             'guarantor_count' => 1
            //         ]
            //     ];
            // }
        } catch (Exception $e) {
            Log::error('Error loading related party loans: ' . $e->getMessage());
            $this->relatedPartyLoans = [];
        }
    }

    public function calculateInsiderStatistics()
    {
        $allInsiderLoans = collect($this->insiderLoans)->merge($this->relatedPartyLoans);
        
        $this->insiderLoanCount = $allInsiderLoans->count();
        $this->totalInsiderLoanAmount = $allInsiderLoans->sum('loan_amount');
        $this->averageInsiderLoanAmount = $this->insiderLoanCount > 0 ? $this->totalInsiderLoanAmount / $this->insiderLoanCount : 0;
    }

    public function categorizeInsiderLoans()
    {
        $allLoans = collect($this->insiderLoans)->merge($this->relatedPartyLoans);
        
        // Initialize with default values to prevent undefined array key errors
        $this->insiderCategories = [
            'employees' => [
                'count' => 0,
                'total_amount' => 0,
                'average_amount' => 0
            ],
            'board_members' => [
                'count' => 0,
                'total_amount' => 0,
                'average_amount' => 0
            ],
            'family_members' => [
                'count' => 0,
                'total_amount' => 0,
                'average_amount' => 0
            ],
            'other_related_parties' => [
                'count' => 0,
                'total_amount' => 0,
                'average_amount' => 0
            ]
        ];

        // Calculate actual values
        if (!empty($this->insiderLoans)) {
            $this->insiderCategories['employees'] = [
                'count' => count($this->insiderLoans),
                'total_amount' => collect($this->insiderLoans)->sum('loan_amount'),
                'average_amount' => count($this->insiderLoans) > 0 ? collect($this->insiderLoans)->sum('loan_amount') / count($this->insiderLoans) : 0
            ];
        }

        if (!empty($allLoans)) {
            $boardMembers = $allLoans->where('relationship_type', 'Board Member');
            $this->insiderCategories['board_members'] = [
                'count' => $boardMembers->count(),
                'total_amount' => $boardMembers->sum('loan_amount'),
                'average_amount' => $boardMembers->count() > 0 ? $boardMembers->sum('loan_amount') / $boardMembers->count() : 0
            ];

            $familyMembers = $allLoans->where('relationship_type', 'Family Member');
            $this->insiderCategories['family_members'] = [
                'count' => $familyMembers->count(),
                'total_amount' => $familyMembers->sum('loan_amount'),
                'average_amount' => $familyMembers->count() > 0 ? $familyMembers->sum('loan_amount') / $familyMembers->count() : 0
            ];

            $otherParties = $allLoans->whereNotIn('relationship_type', ['Employee', 'Board Member', 'Family Member']);
            $this->insiderCategories['other_related_parties'] = [
                'count' => $otherParties->count(),
                'total_amount' => $otherParties->sum('loan_amount'),
                'average_amount' => $otherParties->count() > 0 ? $otherParties->sum('loan_amount') / $otherParties->count() : 0
            ];
        }
    }

    public function checkCompliance()
    {
        $allLoans = collect($this->insiderLoans)->merge($this->relatedPartyLoans);
        $excessiveLoans = $allLoans->where('loan_amount', '>', $this->maximumInsiderLoanLimit);
        
        if ($excessiveLoans->count() > 0) {
            $this->complianceStatus = 'NON-COMPLIANT';
        } else {
            $this->complianceStatus = 'COMPLIANT';
        }
    }

    public function calculateOutstandingBalance($loanId)
    {
        $totalPaid = DB::table('loans_schedules')
            ->where('loan_id', $loanId)
            ->sum('payment');
        
        $loan = LoansModel::find($loanId);
        return $loan ? $loan->principle - ($totalPaid ?: 0) : 0;
    }

    public function calculateDaysInArrears($loanId)
    {
        $maxDaysInArrears = DB::table('loans_schedules')
            ->where('loan_id', $loanId)
            ->where('days_in_arrears', '>', 0)
            ->max('days_in_arrears');
        
        return $maxDaysInArrears ?: 0;
    }

    public function getApprovalStatus($loanId)
    {
        // Check if loan was approved by appropriate authority
        $approval = DB::table('loan_approvals')
            ->where('loan_id', $loanId)
            ->where('status', 'APPROVED')
            ->first();
        
        return $approval ? 'Approved' : 'Pending';
    }

    public function getGuarantorCount($loanId)
    {
        return DB::table('loan_guarantors')
            ->where('loan_id', $loanId)
            ->count();
    }

    public function getInsiderLoanTrend()
    {
        // Get insider loan trend for the last 12 months
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthlyLoans = LoansModel::whereHas('client', function($query) {
                $query->whereIn('client_number', function($subQuery) {
                    $subQuery->select('employee_id')
                        ->from('employees')
                        ->whereNotNull('employee_id');
                });
            })->whereBetween('disbursement_date', [$monthStart, $monthEnd])->get();

            $trend[] = [
                'month' => $date->format('M Y'),
                'loan_count' => $monthlyLoans->count(),
                'total_amount' => $monthlyLoans->sum('principle'),
                'average_amount' => $monthlyLoans->count() > 0 ? $monthlyLoans->sum('principle') / $monthlyLoans->count() : 0
            ];
        }

        return $trend;
    }

    public function getInsiderLoanByDepartment()
    {
        $departmentLoans = [];
        
        foreach ($this->insiderLoans as $loan) {
            $department = $loan['employee_department'];
            if (!isset($departmentLoans[$department])) {
                $departmentLoans[$department] = [
                    'department' => $department,
                    'count' => 0,
                    'total_amount' => 0,
                    'average_amount' => 0
                ];
            }
            $departmentLoans[$department]['count']++;
            $departmentLoans[$department]['total_amount'] += $loan['loan_amount'];
        }

        // Calculate averages
        foreach ($departmentLoans as &$dept) {
            $dept['average_amount'] = $dept['count'] > 0 ? $dept['total_amount'] / $dept['count'] : 0;
        }

        return array_values($departmentLoans);
    }

    public function exportToPdf()
    {
        $this->isExportingPdf = true;
        
        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            // Validate that we have data to export
            if (empty($this->insiderLoans) && empty($this->relatedPartyLoans)) {
                throw new Exception('No data available to export');
            }

            $filename = 'loans_to_insiders_report_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            
            Log::info('Loans to Insiders Report exported as PDF', [
                'format' => 'pdf',
                'report_date' => $this->reportDate,
                'insider_loan_count' => $this->insiderLoanCount,
                'total_insider_loan_amount' => $this->totalInsiderLoanAmount,
                'compliance_status' => $this->complianceStatus,
                'user_id' => auth()->id()
            ]);
            
            // Generate PDF using the export class
            $pdfExport = new LoansToInsidersReportPdfExport(
                $this->insiderLoans,
                $this->relatedPartyLoans,
                $this->insiderCategories,
                $this->reportDate,
                $this->totalInsiderLoanAmount,
                $this->insiderLoanCount,
                $this->averageInsiderLoanAmount,
                $this->complianceStatus,
                $this->maximumInsiderLoanLimit
            );
            
            $pdf = $pdfExport->generate();
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
            
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting PDF: ' . $e->getMessage());
            Log::error('Loans to Insiders Report PDF export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingPdf = false;
        }
    }

    public function exportToExcel()
    {
        $this->isExportingExcel = true;
        
        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            // Validate that we have data to export
            if (empty($this->insiderLoans) && empty($this->relatedPartyLoans)) {
                throw new Exception('No data available to export');
            }

            $filename = 'loans_to_insiders_report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            Log::info('Loans to Insiders Report exported as Excel', [
                'format' => 'excel',
                'report_date' => $this->reportDate,
                'insider_loan_count' => $this->insiderLoanCount,
                'total_insider_loan_amount' => $this->totalInsiderLoanAmount,
                'compliance_status' => $this->complianceStatus,
                'user_id' => auth()->id()
            ]);
            
            return Excel::download(
                new LoansToInsidersReportExport(
                    $this->insiderLoans,
                    $this->relatedPartyLoans,
                    $this->insiderCategories,
                    $this->reportDate
                ),
                $filename
            );
            
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting Excel: ' . $e->getMessage());
            Log::error('Loans to Insiders Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    public function updatedReportDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.loans-to-insiders-report');
    }
}
