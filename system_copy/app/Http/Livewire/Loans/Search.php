<?php

namespace App\Http\Livewire\Loans;

use App\Models\BranchesModel;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class Search extends LivewireDatatable
{
    public $exportable = true;

    public $loanIds = [];

    protected $listeners = ['loanIdsFetched' => '$refresh'];

    public function builder()
    {
        $loanIds = Session::get('loan_ids');
        return LoansModel::query()->whereIn('id', $loanIds);
    }

    /**
     * Emit event to refresh client list component.
     *
     * @param int $memberId
     */
    public function viewClient($memberId)
    {
        Session::put('memberToViewId', $memberId);
        $this->emit('refreshClientsListComponent');
    }

    public function columns(): array
    {
        return [
            Column::name('loan_account_number')->label('Loan Account Number'),
            Column::name('client_number', 'id')->label('Member Number'),

            // Fetching client name via callback
            Column::callback(['client_number'], function ($member_number) {
                $client = DB::table('clients')->select('first_name', 'middle_name', 'last_name')
                    ->where('client_number', $member_number)->first();

                return $client ? "{$client->first_name} {$client->middle_name} {$client->last_name}" : 'N/A';
            })->label('Member Name'),

            // Branch Name
            Column::callback(['branch_id'], function ($branch_id) {
                return BranchesModel::where('id', $branch_id)->value('name');
            })->label('Branch'),

            // Loan Product Name
            Column::callback(['loan_sub_product'], function ($prod_id) {
                return DB::table('loan_sub_products')->where('sub_product_id', $prod_id)->value('sub_product_name');
            })->label('Loan Product'),

            Column::callback('principle', fn($principle) => number_format($principle, 2))
                ->label('Principle (TZS)')
                ->searchable(),

            Column::callback('interest', fn($interest) => $interest . '%')
                ->label('Interest'),

            // Loan Officer Name
            Column::callback('supervisor_id', function ($supervisor) {
                $employee = Employee::find($supervisor);
                return $employee ? "{$employee->first_name} {$employee->middle_name} {$employee->last_name}" : 'N/A';
            })->label('Loan Officer'),

            Column::name('loan_type_2')->label('Loan Type'),

            Column::callback('status', fn($status) => $status)
                ->label('Stage')
                ->searchable(),

            // Actions: View and Delete Buttons
            Column::callback('id', function ($id) {
                $member_number = LoansModel::where('id', $id)->value('client_number');

                $html = '<div class="flex items-center space-x-4 flex-lg-row">
                            <button wire:click="viewloan(' . $id . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900 bg-white rounded-md border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700 inline-flex items-center dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="hidden text-black m-2">View</span>
                            </button>';

                if (session()->get('loanStageId') == 2 && in_array("Create, edit, and delete loan accounts", session()->get('permission_items'))) {
                    $html .= '<button wire:click="deleteLoanModal(' . $id . ',' . $member_number . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900 bg-white rounded-md border border-red-300 hover:bg-red-100 focus:ring-4 text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5 text-red-500 hover:text-red-700 hover:bg-red-100 focus:ring-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="hidden text-customPurple m-2">Delete</span>
                              </button>';
                }

                $html .= '</div>';
                return $html;
            })->label('View'),
        ];
    }



    /**
     * View loan details and emit events to update other components.
     *
     * @param int $id
     */
    public function viewloan($id)
    {
        $loan = LoansModel::select('client_number', 'loan_type')->where('id', $id)->first();

        Session::forget('currentloanClient');
        Session::forget('currentloanID');

        Session::put('currentloanClient', $loan->client_number);
        Session::put('currentloanID', $id);

        $this->emit('refreshClientInfoPage');
        session()->put('loan_type', $loan->loan_type);
        $this->emit('viewClientDetails');
    }
}
