<div>
    <div>
        <div>
            @if (session()->has('message'))
                <div class="bg-green-500 text-white p-2 mb-3">
                    {{ session('message') }}
                </div>
            @endif

            <div class="w-full flex gap-4 p-4">
                <div class="w-3/4 bg-white rounded-md p-2">
                    <table class="w-full text-sm text-left text-gray-500 mb-4">
                        <thead class="text-xs text-black bg-blue-100 dark:text-white w-full">
                        <tr class="border md:border-none block md:table-row w-full">
                            <th class="p-2 border-r px-6 py-4  text-xs text-black ">Name</th>
                            <th class="p-2 border-r px-6 py-4  text-xs text-black ">Type</th>
                            <th class="p-2 border-r px-6 py-4  text-xs text-black text-right"> Value/Percent </th>
                            <th class="p-2 border-r px-6 py-4  text-xs text-black ">parent account code </th>
                            <th class="p-2 border-r px-6 py-4  text-xs text-black text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="block md:table-row-group">
                        @foreach($intangibleAssets as $asset)
                            <tr class="border md:border-none block md:table-row bg-gray-200 text-black uppercase w-full">
                                <td class="p-2 whitespace-nowrap px-6 py-2 text-left  text-xs">{{ $asset->name }}</td>
                                <td class="p-2 whitespace-nowrap px-6 py-2 text-left  text-xs">{{ $asset->type }}</td>
                                <td class="p-2 whitespace-nowrap px-6 py-2 text-right  text-xs"> {{ number_format($asset->value, 2) }}  </td>
                                <td class="p-2 whitespace-nowrap px-6 py-2 text-left  text-xs">{{ $asset->calculating_type }}</td>
                                <td class="p-2 whitespace-nowrap px-6 py-2 text-right  text-xs">
                                    <button wire:click="edit({{ $asset->id }})" class="bg-yellow-500 text-white px-2 py-1 rounded-md">Edit</button>
                                    <button wire:click="delete({{ $asset->id }})" class="bg-red-500 text-white px-2 py-1  rounded-md">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="w-1/4 bg-white rounded-md p-4">



                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">

                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Type of Charge</label>
                            <select wire:model="type" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full
                        p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Select Type  </option>
                                <option value="Loan">Loan</option>
                                <option value="Deposit"> Deposit</option>
                                <option value="Withdraw"> Withdraw</option>

                            </select>
                            @error('type') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name of Charge </label>
                            <input type="text" wire:model="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full
                        p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">

                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Charge Type</label>

                            <select wire:model="calculating_type" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full
                        p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Select Type  </option>
                                <option value="Fixed">Fixed </option>
                                <option value="Percent"> Percent</option>
                            </select>

                            @error('calculating_type') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">

                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Value </label>
                            <input type="number" wire:model="value" step="0.01" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full
                        p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            @error('value') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>


                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"> Income  Account</label>
                            <select wire:model="parent_account_code" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full
                        p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Select Account</option>
                                {{-- Get loan charge/fee related accounts from accounts table --}}
                                @foreach(\Illuminate\Support\Facades\DB::table('accounts')
                                    ->where(function($query) {
                                        $query->where('account_name', 'like', '%loan%charge%')
                                              ->orWhere('account_name', 'like', '%loan%fee%')
                                              ->orWhere('account_name', 'like', '%processing%fee%')
                                              ->orWhereIn('category_code', [4200, 4300, 4400]); // Income from fees and charges
                                    })
                                    ->orderBy('account_name')
                                    ->get() as $account)
                                    <option value="{{$account->category_code}}">{{$account->account_name}}</option>
                                @endforeach
                            </select>

                            @error('parent_account_code') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>


              @if($parent_account_code)

                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Charges  Group Account </label>
                            <select wire:model="category_code" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full
                        p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Select Account</option>

                                @foreach(\Illuminate\Support\Facades\DB::table('accounts')->where('category_code',$this->parent_account_code)->get() as $accounts)
                                    <option value="{{$accounts->sub_category_code}}">{{$accounts->account_name}}</option>
                                @endforeach

                            </select>
                            @error('category_code') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                   @endif

                        <button type="submit" class="bg-blue-900 text-white px-4 py-2 justify-end rounded-md">{{ $isEdit ? 'Update' : 'Register' }} Asset</button>
                    </form>


                </div>
            </div>






            <hr class="my-4">


        </div>
    </div>

</div>
