<?php


namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;

use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class Statement extends LivewireDatatable
{
    protected $listeners = ['refleshStatementTable' => '$refresh'];
    public $exportable = true;

    public function builder()
    {
        $code = Session::get('account_sub_category_code');

       // dd($code);
        // Ensure the GeneralLedger model exists and is properly imported
        // return AccountsModel::query()->where('sub_category_code', $code);
        return AccountsModel::query()->where('parent_account_number', $code);
    }

    /**
     * Define columns for the datatable
     *
     * @return array
     */
    public function columns(): array
    {
        return [

            Column::index($this),


            Column::name('account_name')
                ->label('Account Name')
                ->searchable(),

            Column::name('account_number')
               ->label('Account Number')->searchable(),


                Column::callback(['balance'], function ($balance) {
                    return number_format($balance, 2);
                })->label('balance')
                    ->searchable(),


            Column::callback(['credit'], function ($credit) {
              if(!empty($credit)){
                return number_format($credit, 2);

              }else{
                return 0;
              }
                })->label('Credit')
                    ->searchable(),
            // Format    the debit column
            Column::callback(['debit'], function ($debit) {
                return number_format((double)$debit, 2);
            })->label('Debit')
                ->searchable(),


                Column::callback('id', function ($id)  {


                    $html = '


                    <div class="flex items-center space-x-4 flex-lg-row">
                      <button wire:click="viewAccount(' . $id . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900
                      bg-white rounded-md border border-gray-300 hover:bg-gray-100 focus:ring-4
                      focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                      dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700 items-center inline-flex
                      dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                          <span class="hidden text-black m-2">View</span>

                      </button>
                      </div> ';

                      return $html;
                    })->label('view'),




        ];
    }

    /**
     * Calculate the opening balance
     *
     * @return float
     */

     function viewAccount($id){

        session()->put('accountId',$id);
        $account_number=AccountsModel::find($id)->account_number;
        session()->put('account_number',$account_number);

        $this->emit('viewAccount');
     }
    private function getOpeningBalance(): float
    {
        $code = Session::get('account_sub_category_code');

        // Get all transactions before the first record's date
        $openingDebits = general_ledger::where('sub_category_code', $code)
            ->where('created_at', '<', $this->builder()->min('created_at'))
            ->sum('debit');

        $openingCredits = general_ledger::where('sub_category_code', $code)
            ->where('created_at', '<', $this->builder()->min('created_at'))
            ->sum('credit');

        // Calculate opening balance (you may need to adjust the formula based on your accounting method)
        return $openingCredits - $openingDebits;
    }

    /**
     * Calculate the closing balance
     *
     * @return float
     */
    private function getClosingBalance(): float
    {
        $code = Session::get('account_sub_category_code');

        // Get all debits and credits up to the last transaction
        $totalDebits = general_ledger::where('sub_category_code', $code)->sum('debit');
        $totalCredits = general_ledger::where('sub_category_code', $code)->sum('credit');

        // Calculate closing balance
        $openingBalance = $this->getOpeningBalance();
        return $openingBalance + ($totalCredits - $totalDebits);
    }
}
