<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class CapitalChange extends Component
{
    public string $content = '';
    public $entries;
    public $selectedEntry;
    public bool $loading = true;
    public $selectedEntryToSet;
    public $selectedAccountToSet;
    public $amount;

    protected $rules = [
        'content' => 'required|min:3',
        'selectedEntryToSet' => 'required|exists:entries,id',
        'selectedAccountToSet' => 'required|exists:accounts,id',
        'amount' => 'required|numeric|min:0',
    ];

    protected $listeners  = ['generateExcel'];


    public function mount()
    {
        try {
            $this->entries = DB::table('entries')->get();
            $this->loading = false;
        } catch (\Exception $e) {
            \Log::error('Error fetching entries: ' . $e->getMessage());
            $this->entries = collect();
            $this->loading = false;
            session()->flash('error', 'Failed to load entries. Please try again later.');
        }
    }


    public function generateExcel($tableData)
    {
        // The data passed from the front end will be in the $tableData variable

        // Create an anonymous export class with the table data
        $export = new class($tableData) implements FromArray {
            private $data;
            public function __construct(array $data) {
                $this->data = $data;
            }
            public function array(): array {
                return $this->data;
            }
        };

        // Generate and download the Excel file
        return Excel::download($export, 'table_data.xlsx');
    }


    public function save()
    {
        // Validate only the 'content' field
        $this->validateOnly('content');

        try {
            DB::table('entries')->insert([
                'content' => $this->content,
            ]);

            //$this->reset('content');
            session()->flash('message', 'Entry saved successfully!');
        } catch (\Exception $e) {
            \Log::error('Error saving entry: ' . $e->getMessage());
            session()->flash('error', 'Failed to save entry. Please try again.');
        }
    }

    public function saveAmount()
    {
        $validatedData = $this->validate([
            'selectedEntryToSet' => 'required|exists:entries,id',
            'selectedAccountToSet' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::table('entries_amount')->updateOrInsert(
                [
                    'entry_id' => $validatedData['selectedEntryToSet'],
                    'account_id' => $validatedData['selectedAccountToSet'],
                ],
                [
                    'amount' => $validatedData['amount'],
                ]
            );

            $this->reset('selectedEntryToSet', 'selectedAccountToSet', 'amount');
            session()->flash('message', 'Amount updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error saving amount: ' . $e->getMessage());
            session()->flash('error', 'Failed to update amount. Please try again.');
        }
    }




    #[ArrayShape(['this_year' => "float", 'last_year' => "float", 'year_before_last' => "float"])] public function calculateSurplusAfterTax(): array
    {
        $taxRate = 0.30; // Example tax rate

        // Retrieve all income and expense accounts
        $incomeAccounts = DB::table('income_accounts')->get();
        $expenseAccounts = DB::table('expense_accounts')->get();

        // Initialize overall totals
        $overallThisYearTotal = 0;
        $overallLastYearTotal = 0;
        $overallYearBeforeLastTotal = 0;

        // Calculate income totals
        foreach ($incomeAccounts as $incomeAccount) {
            $thisYearAmount = $this->calculateYearlyTotal($incomeAccount->category_name, date('Y'));
            $lastYearAmount = $this->calculateYearlyTotal($incomeAccount->category_name, date('Y') - 1);
            $yearBeforeLastAmount = $this->calculateYearlyTotal($incomeAccount->category_name, date('Y') - 2);

            $overallThisYearTotal += $thisYearAmount;
            $overallLastYearTotal += $lastYearAmount;
            $overallYearBeforeLastTotal += $yearBeforeLastAmount;
        }

        // Calculate expense totals
        foreach ($expenseAccounts as $expenseAccount) {
            $thisYearAmount = $this->calculateYearlyTotal($expenseAccount->category_name, date('Y'));
            $lastYearAmount = $this->calculateYearlyTotal($expenseAccount->category_name, date('Y') - 1);
            $yearBeforeLastAmount = $this->calculateYearlyTotal($expenseAccount->category_name, date('Y') - 2);

            $overallThisYearTotal -= $thisYearAmount;
            $overallLastYearTotal -= $lastYearAmount;
            $overallYearBeforeLastTotal -= $yearBeforeLastAmount;
        }

        // Calculate tax
        $taxThisYear = $overallThisYearTotal * $taxRate;
        $taxLastYear = $overallLastYearTotal * $taxRate;
        $taxYearBeforeLast = $overallYearBeforeLastTotal * $taxRate;

        // Calculate surplus after tax
        $surplusThisYear = $overallThisYearTotal - $taxThisYear;
        $surplusLastYear = $overallLastYearTotal - $taxLastYear;
        $surplusYearBeforeLast = $overallYearBeforeLastTotal - $taxYearBeforeLast;

        // Return the results
        return [
            'this_year' => $surplusThisYear,
            'last_year' => $surplusLastYear,
            'year_before_last' => $surplusYearBeforeLast,
        ];
    }

    private function calculateYearlyTotal($categoryName, $year)
    {
        $categoryAccounts = DB::table($categoryName)->get();
        $total = 0;

        foreach ($categoryAccounts as $categoryAccount) {
            $total += DB::table('accounts')
                ->where('sub_category_code', $categoryAccount->sub_category_code)
                ->whereYear('created_at', $year)
                ->sum('balance');
        }

        return $total;
    }





    public function render()
    {
        return view('livewire.accounting.capital-change');
    }
}
