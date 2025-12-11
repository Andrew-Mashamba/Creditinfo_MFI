<?php

namespace App\Exports\Savings;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FullReportExport implements WithMultipleSheets
{
    protected $summaryData;
    protected $accountsData;
    protected $membersData;
    protected $monthlyTotals;
    protected $nonCompliantMembers;
    protected $month;
    protected $year;

    public function __construct($summaryData, $accountsData, $membersData, $monthlyTotals, $nonCompliantMembers, $month, $year)
    {
        $this->summaryData = $summaryData;
        $this->accountsData = $accountsData;
        $this->membersData = $membersData;
        $this->monthlyTotals = $monthlyTotals;
        $this->nonCompliantMembers = $nonCompliantMembers;
        $this->month = $month;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Add Summary Sheet
        $sheets[] = new SummaryExport($this->summaryData, $this->month, $this->year);

        // Add Accounts Sheet
        if ($this->accountsData && count($this->accountsData) > 0) {
            $sheets[] = new AccountsExport($this->accountsData, $this->month, $this->year);
        }

        // Add Members Sheet
        if ($this->membersData && count($this->membersData) > 0) {
            $sheets[] = new MembersExport($this->membersData, $this->month, $this->year);
        }

        // Add Monthly Trends Sheet
        if ($this->monthlyTotals && count($this->monthlyTotals) > 0) {
            $sheets[] = new MonthlyTrendsExport($this->monthlyTotals, $this->year);
        }

        // Add Non-Compliant Members Sheet
        if ($this->nonCompliantMembers && count($this->nonCompliantMembers) > 0) {
            $sheets[] = new NonCompliantMembersExport($this->nonCompliantMembers, $this->month, $this->year);
        }

        return $sheets;
    }
}