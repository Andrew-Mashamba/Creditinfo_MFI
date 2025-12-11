<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Approved Expenses</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($expenses->isEmpty())
        <p class="text-gray-500">No approved expenses found.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Expense Type</th>
                        <th scope="col" class="px-6 py-3">Amount</th>
                        <th scope="col" class="px-6 py-3">Payment Type</th>
                        <th scope="col" class="px-6 py-3">Description</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $expense->account->name }}</td>
                            <td class="px-6 py-4">{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $expense->payment_type)) }}</td>
                            <td class="px-6 py-4">{{ $expense->description }}</td>
                            <td class="px-6 py-4">
                                @if(empty($expense->retirement_receipt_path))
                                    <button 
                                        wire:click="openRetirementUpload({{ $expense->id }})"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Upload Retirement Receipt
                                    </button>
                                @else
                                    <span class="text-green-600">Receipt Uploaded</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($selected_expense_id)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upload Retirement Receipt</h3>
                        
                        <form wire:submit.prevent="uploadRetirementReceipt">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Receipt File (PDF, JPG, PNG)
                                </label>
                                <input 
                                    type="file" 
                                    wire:model="retirement_receipt" 
                                    accept=".pdf,.jpg,.jpeg,.png" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                @error('retirement_receipt')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button 
                                    type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Upload
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="$set('selected_expense_id', null)" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 