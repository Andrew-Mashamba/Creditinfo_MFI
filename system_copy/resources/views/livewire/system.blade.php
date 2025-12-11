<div>
    <!-- Error Message -->
    @if($error)
        <div class="fixed top-4 right-4 z-50 animate-fade-in-down">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ $error }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="w-full p-2">
        <div class="flex items-center justify-center min-h-screen bg-white w-full rounded-md">
            <div class="text-center m-auto">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="#2D3D88" class="w-16 h-16 animate-spin">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-8 h-8 bg-white rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div wire:loading.remove class="transition-all duration-300 ease-in-out">
        <div class="animate-fade-in">
            @switch($this->menu_id)
                @case('0')
                    <livewire:dashboard.dashboard />
                    @break

                @case('1')
                    <livewire:branches.branches />
                    @break

                @case('2')
                    <livewire:clients.clients />
                    @break

                @case('3')
                    <livewire:shares.shares />
                    @break

                @case('4')
                    <livewire:savings.savings />
                    @break

                @case('5')
                    <livewire:deposits.deposits />
                    @break

                @case('6')
                    <livewire:loans.loans />
                    @break

                @case('7')
                    <livewire:products-management.products />
                    @break

                @case('8')
                    <livewire:accounting.accounting />
                    @break

                @case('181')
                    <livewire:services.services />
                    @break

                @case('9')
                    <div class="p-4">
                        <livewire:expenses.expense />
                    </div>
                    @break

                @case('10')
                    <livewire:payments.payments />
                    @break

                @case('11')
                    <livewire:investment.investment />
                    @break

                @case('12')
                    <livewire:procurement.procurement />
                    @break

                @case('13')
                    <livewire:budget-management.budget />
                    @break

                @case('14')
                    <livewire:insurance.insurance />
                    @break

                @case('15')
                    <livewire:teller-management.teller />
                    @break

                @case('16')
                    <livewire:reconciliation.reconciliation />
                    @break

                @case('17')
                    <livewire:h-r.dashboard />
                    @break

                @case('18')
                    <livewire:self-services.self-services />
                    @break

                @case('19')
                    @livewire('approvals.approvals', [], key('approvals-' . $menu_id . '-' . now()->timestamp))
                    @break

                @case('20')
                    <livewire:reports.reports />
                    @break

                @case('21')
                    <livewire:profile-setting.profile />
                    @break

                @case('22')
                    <livewire:users.dashboard />
                    @break

                @case('23')
                    <livewire:active-loan.all-loan />
                    @break

                @case('24')
                    <livewire:management.management />
                    @break

                @case('26')
                    <livewire:cash-management.cash-management />
                    @break

                    @case('27')
                    <livewire:billing.billing />
                    @break

                    @case('28')
                    <livewire:transactions.transactions />
                    @break

                @case('29')
                    <livewire:members-portal.members-portal />
                    @break

                @case('30')
                    <livewire:email.email-outlook />
                    @break

                @case('31')
                    <livewire:subscriptions.subscriptions />
                    @break

                @default
                    <livewire:dashboard.dashboard />
            @endswitch
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function () {
            Livewire.on('redirect', function(data) {
                window.location.href = data.url;
            });
        });
    </script>

    <style nonce="{{ csp_nonce() }}">
        @keyframes fade-in-down {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.3s ease-out;
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</div>

