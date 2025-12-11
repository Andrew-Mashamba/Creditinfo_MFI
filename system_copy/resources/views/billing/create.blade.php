<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Bill') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('billing.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Member Selection -->
                            <div>
                                <label for="member_id" class="block text-sm font-medium text-gray-700">Member</label>
                                <select name="member_id" id="member_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                    <option value="">Select Member</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->member_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Service Selection -->
                            <div>
                                <label for="service_id" class="block text-sm font-medium text-gray-700">Service</label>
                                <select name="service_id" id="service_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                    <option value="">Select Service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" 
                                            data-is-mandatory="{{ $service->is_mandatory }}"
                                            data-lower-limit="{{ $service->lower_limit }}"
                                            data-upper-limit="{{ $service->upper_limit }}"
                                            {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                                <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                @error('due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Mode -->
                            <div>
                                <label for="payment_mode" class="block text-sm font-medium text-gray-700">Payment Mode</label>
                                <select name="payment_mode" id="payment_mode" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                    <option value="">Select Payment Mode</option>
                                    <option value="1" {{ old('payment_mode') == '1' ? 'selected' : '' }}>Partial</option>
                                    <option value="2" {{ old('payment_mode') == '2' ? 'selected' : '' }}>Full</option>
                                    <option value="3" {{ old('payment_mode') == '3' ? 'selected' : '' }}>Exact</option>
                                    <option value="4" {{ old('payment_mode') == '4' ? 'selected' : '' }}>Limited</option>
                                    <option value="5" {{ old('payment_mode') == '5' ? 'selected' : '' }}>Infinity</option>
                                </select>
                                @error('payment_mode')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Recurring -->
                            <div>
                                <label for="is_recurring" class="block text-sm font-medium text-gray-700">Recurring</label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="is_recurring" value="1" class="form-checkbox h-4 w-4 text-blue-600" {{ old('is_recurring') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">This is a recurring bill</span>
                                    </label>
                                </div>
                                @error('is_recurring')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Bill
                            </button>
                            <a href="{{ route('billing.index') }}" class="ml-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_id');
            const amountInput = document.getElementById('amount');
            const paymentModeSelect = document.getElementById('payment_mode');

            function updateAmountConstraints() {
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                if (selectedOption) {
                    const lowerLimit = parseFloat(selectedOption.dataset.lowerLimit);
                    const upperLimit = parseFloat(selectedOption.dataset.upperLimit);
                    const isMandatory = selectedOption.dataset.isMandatory === '1';

                    amountInput.min = lowerLimit;
                    if (upperLimit) {
                        amountInput.max = upperLimit;
                    }

                    if (isMandatory) {
                        paymentModeSelect.value = '2'; // Full payment
                        paymentModeSelect.disabled = true;
                    } else {
                        paymentModeSelect.disabled = false;
                    }
                }
            }

            serviceSelect.addEventListener('change', updateAmountConstraints);
            updateAmountConstraints();
        });
    </script>
    @endpush
</x-app-layout> 