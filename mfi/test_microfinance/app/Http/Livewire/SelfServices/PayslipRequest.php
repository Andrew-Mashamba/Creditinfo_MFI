<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayslipRequest extends Component
{
    public $selectedTab = 'current'; // current, history, tax
    public $selectedYear = '';
    public $selectedMonth = '';
    public $payslips = [];
    public $currentPayslip = null;
    public $taxSummary = [];
    public $yearToDateEarnings = [];

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year;
        $this->loadPayslipData();
    }

    public function loadPayslipData()
    {
        // Sample current payslip
        $this->currentPayslip = [
            'month' => Carbon::now()->format('F Y'),
            'employee_id' => 'EMP001234',
            'payment_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            'earnings' => [
                ['description' => 'Basic Salary', 'amount' => 1500000],
                ['description' => 'House Allowance', 'amount' => 450000],
                ['description' => 'Transport Allowance', 'amount' => 200000],
                ['description' => 'Medical Allowance', 'amount' => 150000],
                ['description' => 'Overtime Pay', 'amount' => 75000],
            ],
            'deductions' => [
                ['description' => 'PAYE Tax', 'amount' => 356250],
                ['description' => 'NSSF Employee', 'amount' => 142500],
                ['description' => 'Health Insurance', 'amount' => 50000],
                ['description' => 'Staff Loan', 'amount' => 100000],
                ['description' => 'SACCOS Savings', 'amount' => 200000],
            ],
            'gross_pay' => 2375000,
            'total_deductions' => 848750,
            'net_pay' => 1526250,
            'bank_account' => '**** **** **** 1234',
            'payment_method' => 'Bank Transfer'
        ];

        // Sample payslip history
        $this->payslips = [
            [
                'id' => 1,
                'month' => 'June 2025',
                'payment_date' => '2025-06-30',
                'gross_pay' => 2375000,
                'net_pay' => 1526250,
                'status' => 'paid',
                'downloaded' => true
            ],
            [
                'id' => 2,
                'month' => 'May 2025',
                'payment_date' => '2025-05-31',
                'gross_pay' => 2300000,
                'net_pay' => 1476250,
                'status' => 'paid',
                'downloaded' => true
            ],
            [
                'id' => 3,
                'month' => 'April 2025',
                'payment_date' => '2025-04-30',
                'gross_pay' => 2300000,
                'net_pay' => 1476250,
                'status' => 'paid',
                'downloaded' => false
            ],
            [
                'id' => 4,
                'month' => 'March 2025',
                'payment_date' => '2025-03-31',
                'gross_pay' => 2300000,
                'net_pay' => 1476250,
                'status' => 'paid',
                'downloaded' => true
            ]
        ];

        // Tax summary for the year
        $this->taxSummary = [
            'year' => $this->selectedYear,
            'gross_income' => 27600000,
            'taxable_income' => 25300000,
            'paye_paid' => 4275000,
            'nssf_paid' => 1710000,
            'tax_rate' => '30%',
            'tax_bracket' => 'Above 720,000/month'
        ];

        // Year to date earnings
        $this->yearToDateEarnings = [
            'gross_ytd' => 16200000,
            'net_ytd' => 10383750,
            'tax_ytd' => 2493750,
            'deductions_ytd' => 5816250,
            'months_worked' => 7
        ];
    }

    public function downloadPayslip($payslipId)
    {
        // Here you would generate and download the PDF
        session()->flash('message', 'Payslip downloaded successfully!');
    }

    public function downloadCurrentPayslip()
    {
        // Here you would generate and download the current month's payslip
        session()->flash('message', 'Current payslip downloaded successfully!');
    }

    public function downloadTaxCertificate()
    {
        // Here you would generate and download the tax certificate
        session()->flash('message', 'Tax certificate downloaded successfully!');
    }

    public function filterByYear()
    {
        $this->loadPayslipData(); // Reload data based on selected year
    }

    public function render()
    {
        return view('livewire.self-services.payslip-request');
    }
}