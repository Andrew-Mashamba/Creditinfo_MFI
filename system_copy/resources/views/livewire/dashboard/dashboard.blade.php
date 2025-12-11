{{-- Dashboard router based on department or user dashboard_type (numeric) --}}
@php
    $user = Auth::user();
    $department = $user->department;

    $dashboardType = $department && $department->dashboard_type ? $department->dashboard_type : ($user->dashboard_type ?? null);
@endphp

<div class="h-full w-full">

    @if (session()->has('message'))
        <div class="alert alert-success bg-blue-100 font-bold text-center justify-center">
            <strong>{{ session('message') }}</strong>
        </div>
    @endif

    <div class="w-full h-full grid justify-items-center">
        <div class="w-full m-auto grid justify-items-center">
            <div class="w-full bg-gray-100 rounded-lg p-2">
  
                {{-- Dashboard Switcher --}}
                @php
                    $dashboardType = 1;
                @endphp

              
                <div>
                    <div class="w-full">
                        <div class="w-full pt-2">
                            @if($permissions['canView'] ?? false)
                                @switch($dashboardType)
                                    @case(1)
                                        @if($permissions['canViewFrontDesk'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.front-desk />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Front Desk Dashboard'])
                                        @endif
                                        @break
                                    @case(2)
                                        @if($permissions['canViewAccountant'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.accountant-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Accountant Dashboard'])
                                        @endif
                                        @break
                                    @case(3)
                                        @if($permissions['canViewLoanOfficer'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.loan-officer-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Loan Officer Dashboard'])
                                        @endif
                                        @break
                                    @case(4)
                                        @if($permissions['canViewBranchManager'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.branch-manager-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Branch Manager Dashboard'])
                                        @endif
                                        @break
                                    @case(5)
                                        @if($permissions['canViewTeller'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.teller-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Teller Dashboard'])
                                        @endif
                                        @break
                                    @case(6)
                                        @if($permissions['canViewMember'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.member-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Member Dashboard'])
                                        @endif
                                        @break
                                    @case(7)
                                        @if($permissions['canViewProcurement'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.procurement-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Procurement Dashboard'])
                                        @endif
                                        @break
                                    @case(8)
                                        @if($permissions['canViewHr'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.hr-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'HR Dashboard'])
                                        @endif
                                        @break
                                    @case(9)
                                        @if($permissions['canViewAuditor'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.auditor-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'Auditor Dashboard'])
                                        @endif
                                        @break
                                    @case(10)
                                        @if($permissions['canViewSystemAdmin'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.system-admin-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'System Admin Dashboard'])
                                        @endif
                                        @break
                                    @default
                                        @if($permissions['canViewCeo'] ?? $permissions['canView'] ?? false)
                                            <livewire:dashboard.ceo-dashboard />
                                        @else
                                            @include('livewire.dashboard.partials.no-permission', ['dashboard' => 'CEO Dashboard'])
                                        @endif
                                @endswitch
                            @else
                                <div class="bg-white rounded-lg p-8 text-center">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Restricted</h3>
                                    <p class="text-gray-600">You don't have permission to access the dashboard.</p>
                                    <p class="text-sm text-gray-500 mt-2">Please contact your administrator if you need access.</p>
                                </div>
                            @endif
                        </div>




                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
