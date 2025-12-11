<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Contracts\View\View;

class IntegratedFinancialStatementsExport implements WithMultipleSheets
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function sheets(): array
    {
        $sheets = [];
        
        // Balance Sheet
        if (isset($this->data['balance_sheet'])) {
            $sheets[] = new BalanceSheetExport($this->data['balance_sheet'], $this->data['period']);
        }
        
        // Income Statement
        if (isset($this->data['income_statement'])) {
            $sheets[] = new IncomeStatementExportSheet($this->data['income_statement'], $this->data['period']);
        }
        
        // Cash Flow Statement
        if (isset($this->data['cash_flow'])) {
            $sheets[] = new CashFlowExport($this->data['cash_flow'], $this->data['period']);
        }
        
        // Equity Statement
        if (isset($this->data['equity_statement'])) {
            $sheets[] = new EquityStatementExport($this->data['equity_statement'], $this->data['period']);
        }
        
        // Financial Ratios
        if (isset($this->data['ratios']) && !empty($this->data['ratios'])) {
            $sheets[] = new FinancialRatiosExport($this->data['ratios'], $this->data['period']);
        }
        
        return $sheets;
    }
}

class BalanceSheetExport implements FromView, WithTitle
{
    protected $data;
    protected $period;
    
    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }
    
    public function view(): View
    {
        return view('exports.balance-sheet-excel', [
            'data' => $this->data,
            'period' => $this->period
        ]);
    }
    
    public function title(): string
    {
        return 'Statement of Financial Position';
    }
}

class IncomeStatementExportSheet implements FromView, WithTitle
{
    protected $data;
    protected $period;
    
    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }
    
    public function view(): View
    {
        return view('exports.income-statement-excel', [
            'data' => $this->data,
            'period' => $this->period
        ]);
    }
    
    public function title(): string
    {
        return 'Income Statement';
    }
}

class CashFlowExport implements FromView, WithTitle
{
    protected $data;
    protected $period;
    
    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }
    
    public function view(): View
    {
        return view('exports.cash-flow-excel', [
            'data' => $this->data,
            'period' => $this->period
        ]);
    }
    
    public function title(): string
    {
        return 'Statement of Cash Flows';
    }
}

class EquityStatementExport implements FromView, WithTitle
{
    protected $data;
    protected $period;
    
    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }
    
    public function view(): View
    {
        return view('exports.equity-statement-excel', [
            'data' => $this->data,
            'period' => $this->period
        ]);
    }
    
    public function title(): string
    {
        return 'Statement of Changes in Equity';
    }
}

class FinancialRatiosExport implements FromView, WithTitle
{
    protected $data;
    protected $period;
    
    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }
    
    public function view(): View
    {
        return view('exports.financial-ratios-excel', [
            'data' => $this->data,
            'period' => $this->period
        ]);
    }
    
    public function title(): string
    {
        return 'Financial Ratios';
    }
}