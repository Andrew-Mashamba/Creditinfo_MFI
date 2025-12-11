<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('GEPG Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:payments.gepg-payment 
                x-data="{
                    init() {
                        // Listen for events from parent components
                        Livewire.on('setBillData', (data) => {
                            @this.setBillData(data);
                        });
                        
                        Livewire.on('setCustomerData', (data) => {
                            @this.setCustomerData(data);
                        });
                    }
                }"
                x-on:bill-verified.window="
                    // Handle bill verification success
                    console.log('Bill verified:', $event.detail);
                "
                x-on:bill-error.window="
                    // Handle bill verification error
                    console.error('Bill error:', $event.detail);
                "
                x-on:payment-success.window="
                    // Handle payment success
                    console.log('Payment success:', $event.detail);
                "
                x-on:payment-error.window="
                    // Handle payment error
                    console.error('Payment error:', $event.detail);
                "
            />
        </div>
    </div>
</x-app-layout> 