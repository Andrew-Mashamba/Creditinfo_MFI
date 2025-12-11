<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Luku Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                // You can get these values from your controller or other sources
                $initialMeterNumber = request()->query('meter_number');
                $initialDebitAccount = request()->query('account_number');
                $initialAmount = request()->query('amount');
                $initialCustomerName = request()->query('customer_name');
                $initialCustomerPhone = request()->query('customer_phone');
            @endphp

            <livewire:payments.luku-payment 
                :initial-meter-number="$initialMeterNumber"
                :initial-debit-account="$initialDebitAccount"
                :initial-amount="$initialAmount"
                :initial-customer-name="$initialCustomerName"
                :initial-customer-phone="$initialCustomerPhone"
            />
        </div>
    </div>
</x-app-layout> 