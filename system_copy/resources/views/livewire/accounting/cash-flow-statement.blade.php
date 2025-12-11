<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden text-xs">
    <div class="px-6 py-4">
        <h2 class="font-bold text-center mb-6">NBC SACCOS LTD</h2>
        <h3 class="font-semibold text-center mb-4">CASH FLOW STATEMENT</h3>
        <p class="text-center mb-8">For the Year Ended 31.12.2023</p>

        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-6 py-3 text-left">Section</th>
                <th class="border border-gray-300 px-6 py-3 text-left">Account</th>
                <th class="border border-gray-300 px-6 py-3 text-left">Operation</th>
                <th class="border border-gray-300 px-6 py-3 text-right">Balance</th>
                <th class="border border-gray-300 px-6 py-3 text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            @php
                $grandTotal = 0;
                $accounts = DB::table('accounts')->get();
            @endphp
            @foreach(['operating', 'investing', 'financing'] as $section)
                @php
                    $sectionTotal = 0;
                @endphp
                <tr>
                    <td class="border border-gray-300 px-6 py-2 font-bold" colspan="5">{{ ucfirst($section) }} Activities</td>
                </tr>

                <!-- Account Dropdown for Each Section -->
                <tr>
                    <td class="border border-gray-300 px-6 py-3">
                        <select wire:model="newAccountId.{{ $section }}" class="w-full p-2 border rounded-lg">
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border border-gray-300 px-6 py-3">
                        <select wire:model="newOperation.{{ $section }}" class="w-full p-2 border rounded-lg">
                            <option value="add">Add</option>
                            <option value="subtract">Subtract</option>
                        </select>
                    </td>
                    <td class="border border-gray-300 px-6 py-3 text-center">
                        <button class="bg-blue-500 text-white px-6 py-2 rounded-lg" wire:click="addRow('{{ $section }}')">Add Account</button>
                    </td>
                </tr>

                <!-- Render Existing Accounts and Their Operations -->
                @foreach($selectedAccounts[$section] as $index => $selectedAccount)
                    @php
                        $balance = $selectedAccount['balance'];
                        $sectionTotal += ($selectedAccount['operation'] == 'add' ? 1 : -1) * $balance;
                    @endphp
                    <tr>
                        <td class="border border-gray-300 px-6 py-3"></td>
                        <td class="border border-gray-300 px-6 py-3">
                            <span>{{ $selectedAccount['account_name'] }}</span>
                            <span class="text-gray-500">({{ number_format($balance, 2) }})</span>
                        </td>
                        <td class="border border-gray-300 px-6 py-3">
                            <select wire:model="selectedAccounts.{{ $section }}.{{ $index }}.operation" class="w-full p-2 border rounded-lg">
                                <option value="add">Add</option>
                                <option value="subtract">Subtract</option>
                            </select>
                        </td>
                        <td class="border border-gray-300 px-6 py-3 text-right">
                            {{ number_format($balance, 2) }}
                        </td>
                        <td class="border border-gray-300 px-6 py-3 text-center">
                            <button class="bg-red-500 text-white px-4 py-2 rounded-lg" wire:click="removeRow('{{ $section }}', {{ $index }})">Remove</button>
                        </td>
                    </tr>
                @endforeach

                <!-- Section Subtotal -->
                <tr class="font-semibold bg-gray-100">
                    <td colspan="3" class="border border-gray-300 px-6 py-3 text-right">Subtotal</td>
                    <td class="border border-gray-300 px-6 py-3 text-right">{{ number_format($sectionTotal, 2) }}</td>
                    <td></td>
                </tr>

                @php
                    $grandTotal += $sectionTotal;
                @endphp
            @endforeach

            <!-- Grand Total -->
            <tr class="font-bold bg-gray-200">
                <td colspan="3" class="border border-gray-300 px-6 py-3 text-right">Grand Total</td>
                <td class="border border-gray-300 px-6 py-3 text-right">{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <div class="mt-6">
            <button wire:click="saveConfiguration" class="bg-green-500 text-white px-6 py-3 rounded-lg">Save Configuration</button>
        </div>
    </div>
</div>
