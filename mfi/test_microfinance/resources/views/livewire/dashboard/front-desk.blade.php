<div>
    <style nonce="{{ csp_nonce() }}">

.circle{
  position: absolute;
  border-radius: 50%;
  background: white;
  animation: ripple 15s infinite;
  box-shadow: 0px 0px 1px 0px #508fb9;
}

.small{
  width: 200px;
  height: 200px;
  left: -100px;
  bottom: -100px;
}

.medium{
  width: 400px;
  height: 400px;
  left: -200px;
  bottom: -200px;
}

.large{
  width: 600px;
  height: 600px;
  left: -300px;
  bottom: -300px;
}

.xlarge{
  width: 800px;
  height: 800px;
  left: -400px;
  bottom: -400px;
}

.xxlarge{
  width: 1000px;
  height: 1000px;
  left: -500px;
  bottom: -500px;
}

.shade1{
  opacity: 0.2;
}
.shade2{
  opacity: 0.5;
}

.shade3{
  opacity: 0.7;
}

.shade4{
  opacity: 0.8;
}

.shade5{
  opacity: 0.9;
}

@keyframes ripple{
  0%{
    transform: scale(0.8);
  }
  
  50%{
    transform: scale(1.2);
  }
  
  100%{
    transform: scale(0.8);
  }
}

    </style>
<div class="min-h-screen ">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">MFI Front Desk</h1>
                        <p class="text-gray-600 mt-1">Member services, transactions, and loan applications</p>
                    </div>
                </div>
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Today's Withdrawals</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ number_format(DB::table('general_ledger')
                                        ->where('record_on_account_number', function ($query) {
                                            $query->select('account_number')
                                                ->from('accounts')
                                                ->where('id', function ($subquery) {
                                                    $subquery->select('account_id')
                                                        ->from('tellers')
                                                        ->where('employee_id', auth()->user()->employeeId);
                                                });
                                        })
                                        ->whereDate('created_at', now())
                                        ->sum('debit')) }} TZS
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Today's Deposits</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ number_format(DB::table('general_ledger')
                                        ->where('record_on_account_number', function ($query) {
                                            $query->select('account_number')
                                                ->from('accounts')
                                                ->where('id', function ($subquery) {
                                                    $subquery->select('account_id')
                                                        ->from('tellers')
                                                        ->where('employee_id', auth()->user()->employeeId);
                                                });
                                        })
                                        ->whereDate('created_at', now())
                                        ->sum('credit')) }} TZS
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Loan Applications</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ number_format(DB::table('loans')->whereDate('created_at',now())->sum('principle')) }} TZS
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-6 ">
            <!-- Main Content Area -->
            <main class="flex-1 ">
          
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-6">
                    <div class="flex items-start space-x-4">
                        <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-red-900 mb-3">Mwanga MFI Limited</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="space-y-1">
                                    <p class="font-semibold text-gray-700">Address:</p>
                                    <p class="text-gray-600">Mikocheni, Dar es Salaam</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="font-semibold text-gray-700">Business Date:</p>
                                    <p class="text-gray-600">{{ date('d/m/Y') }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="font-semibold text-gray-700">Officer:</p>
                                    <p class="text-gray-600">{{ auth()->user()->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account Statement --}}
                @if($item === 'statement' || !$item)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 max-w-3xl mx-auto">
                        <h2 class="text-2xl font-bold text-red-900 mb-6 flex items-center gap-2">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Generate Account Statement
                        </h2>
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="check_account_number" class="block text-sm font-medium text-red-900 mb-2">Account Number</label>
                                    <input wire:model.bounce="check_account_number" type="text" id="check_account_number" 
                                           class="w-full p-3 border border-red-300 rounded-lg bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" placeholder="Enter account number">
                                </div>
                                <div>
                                    <label for="daterange" class="block text-sm font-medium text-red-900 mb-2">Date Range</label>
                                    <input type="text" name="daterange" value="{{$start_date_input }} - {{$end_date_input}}"
                                           class="w-full p-3 border border-red-300 rounded-lg bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors cursor-pointer" placeholder="Select date range">
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-4">
                                <button wire:click.prevent="downloadExcelFile" type="button" 
                                        class="inline-flex items-center px-5 py-3 text-sm font-medium text-white bg-red-600 border border-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>
                                        <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>
                                    </svg>
                                    Download Excel
                                </button>
                                <button wire:click.prevent="downloadPDFFile" type="button" 
                                        class="inline-flex items-center px-5 py-3 text-sm font-medium text-white bg-red-600 border border-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"></path>
                                        <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"></path>
                                    </svg>
                                    Download PDF
                                </button>
                            </div>
                            @if(session()->has('error1'))
                                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-red-700">{{session()->get('error1')}}</span>
                                </div>
                            @endif
                        </form>
                    </div>

                {{-- Member Services Section --}}
                @elseif($item === 'member-services')
        
                @elseif($item === 'member-search')
                    <livewire:dashboard.member-search />
                @elseif($item === 'membership-form')
                    <livewire:dashboard.membership-form />
                @elseif($item === 'application-status')
                    <livewire:dashboard.application-status />
                @elseif($item === 'communication')
                    <livewire:dashboard.communication />
                @elseif($item === 'visitor-logbook')
                    <livewire:dashboard.visitor-logbook />
                @elseif($item === 'document-filing')
                    <livewire:dashboard.document-filing />

                {{-- Cash & Transactions Section --}}
                @elseif($item === 'receive-deposits')
                    <livewire:dashboard.receive-deposits />
                @elseif($item === 'process-withdrawals')
                    <livewire:dashboard.process-withdrawals />
                @elseif($item === 'internal-transfers')
                    <livewire:dashboard.internal-transfers />

                {{-- Account Management Section --}}
                @elseif($item === 'balance-inquiry')
                    <livewire:dashboard.balance-inquiry />
                @elseif($item === 'statement-requests')
                    <livewire:dashboard.statement-requests />
                @elseif($item === 'loan-repayment')
                    <livewire:dashboard.loan-repayment />


                {{-- Support Section --}}
                @elseif($item === 'support-tickets')
                    <livewire:dashboard.support-tickets />

                {{-- Legacy Items --}}
                @elseif($item === 'withdraw')
                    {{-- Withdrawals component placeholder --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                        <h2 class="text-2xl font-bold text-red-900 mb-6">Withdrawals</h2>
                        <p class="text-gray-600">Withdrawals functionality coming soon...</p>
                    </div>
                @elseif($item === 'deposit')
                    {{-- Deposits component placeholder --}}
                        <h2 class="text-2xl font-bold text-red-900 mb-6">Loan Applications</h2>
                        <p class="text-gray-600">Loan applications functionality coming soon...</p>
                    </div>
                @elseif($item === 'loan-application')
                    <livewire:dashboard.loan-application />
                   

                @endif
             
              

            </main>
            
            <!-- Sidebar Navigation -->
            <!-- Enhanced Sidebar Navigation -->
<aside class="w-72 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex-shrink-0 h-[calc(80vh-2rem)] sticky top-4" style="height: 80vh;">
    <nav class="p-4 space-y-4 h-full overflow-y-auto">

         <button 
             wire:click="$set('item', 'loan-application')" 
             class="w-full flex items-center p-3 rounded-2xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 
                    {{ $item === 'loan-application' ? 'bg-red-600 text-white shadow-md' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900' }}"
         >         
             <span class="font-medium text-sm ml-3">Loan Application</span>            
         </button>
        
        <div class="pt-4 border-t border-gray-100">
            <!-- Receptionist Section - Improved with better spacing and icons -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Reception</h3>
                
                                 <div class="space-y-1">
                     <button wire:click="$set('item', 'member-services')" class="w-full flex items-center p-3 rounded-lg text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all duration-200 group {{ $item === 'member-services' ? 'bg-red-50 text-red-600' : '' }}">
                         <div class="p-2 bg-red-100 rounded-lg text-red-600 group-hover:bg-red-200 transition-colors">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                             </svg>
                         </div>
                         <span class="font-medium ml-3">Member Services</span>
                         <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto h-4 w-4 text-gray-400 transform transition-transform duration-200 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                         </svg>
                     </button>
                    
                                         <div class="ml-8 space-y-1 mt-1 pl-2 border-l-2 border-gray-100">
                         <button wire:click="$set('item', 'member-search')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ $item === 'member-search' ? 'bg-gray-50 text-gray-900' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                             </svg>
                             <span>Member Search</span>
                         </button>
                         <button wire:click="$set('item', 'membership-form')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ $item === 'membership-form' ? 'bg-gray-50 text-gray-900' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                             </svg>
                             <span>Institution Forms</span>
                         </button>
             
                         <button wire:click="$set('item', 'communication')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ $item === 'communication' ? 'bg-gray-50 text-gray-900' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                             </svg>
                             <span>Communication</span>
                         </button>
                         <button wire:click="$set('item', 'visitor-logbook')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ $item === 'visitor-logbook' ? 'bg-gray-50 text-gray-900' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                             </svg>
                             <span>Visitor Logbook</span>
                         </button>
             
                     </div>
                </div>
            </div>
            
            <!-- Teller Section - Improved with better organization and visual cues -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Teller Operations</h3>
                
                <!-- Cash & Transactions -->
                <div class="mb-4">
                    <button class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 group">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="font-medium text-sm ml-3 text-gray-700 group-hover:text-gray-900">Cash & Transactions</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transform transition-transform duration-200 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                                         <div class="ml-8 space-y-1 mt-1 pl-2 border-l-2 border-gray-100">
                         <button wire:click="$set('item', 'receive-deposits')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-green-50 hover:text-green-700 transition-colors {{ $item === 'receive-deposits' ? 'bg-green-50 text-green-700' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                             </svg>
                             <span>Receive Deposits/Savings</span>
                         </button>
                         <button wire:click="$set('item', 'process-withdrawals')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-red-50 hover:text-red-700 transition-colors {{ $item === 'process-withdrawals' ? 'bg-red-50 text-red-700' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                             </svg>
                             <span>Process Withdrawals</span>
                         </button>
                         <button wire:click="$set('item', 'internal-transfers')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-red-50 hover:text-red-700 transition-colors {{ $item === 'internal-transfers' ? 'bg-red-50 text-red-700' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                             </svg>
                             <span>Internal Transfers</span>
                         </button>
                     </div>
                </div>
                
                <!-- Account Management -->
                <div class="mb-4">
                    <button class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 group">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            </div>
                            <span class="font-medium text-sm ml-3 text-gray-700 group-hover:text-gray-900">Account Management</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transform transition-transform duration-200 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                                         <div class="ml-8 space-y-1 mt-1 pl-2 border-l-2 border-gray-100">
                         <button wire:click="$set('item', 'balance-inquiry')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-700 transition-colors {{ $item === 'balance-inquiry' ? 'bg-purple-50 text-purple-700' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                             </svg>
                             <span>Balance Inquiry</span>
                         </button>
                         <button wire:click="$set('item', 'statement-requests')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-indigo-50 hover:text-red-900 transition-colors {{ $item === 'statement-requests' ? 'bg-indigo-50 text-red-900' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                             </svg>
                             <span>Statement Requests</span>
                         </button>
                         <button wire:click="$set('item', 'loan-repayment')" class="w-full flex items-center p-2 rounded-md text-sm text-gray-600 hover:bg-orange-50 hover:text-orange-700 transition-colors {{ $item === 'loan-repayment' ? 'bg-orange-50 text-orange-700' : '' }}">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                             </svg>
                             <span>Loan Repayment</span>
                         </button>
                     </div>
                </div>
            </div>
            
             
             
             <!-- Support Section -->
             <div class="mb-4">
                 <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Support</h3>
                 <button wire:click="$set('item', 'support-tickets')" class="w-full flex items-center p-3 rounded-lg text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ $item === 'support-tickets' ? 'bg-gray-50 text-gray-900' : '' }}">
                     <div class="p-2 bg-gray-100 rounded-lg text-gray-600">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                         </svg>
                     </div>
                     <span class="font-medium ml-3">Support Tickets</span>
                     <span class="ml-auto bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full">3 New</span>
                 </button>
             </div>
        </div>
    </nav>
</aside>

            
        </div>
    </div>
</div>
</div>