<div>
    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="$set('activeTab', 'overview')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'approvals')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm relative
                        {{ $activeTab === 'approvals' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Board Approvals
                    @if($pendingBoardApprovals->count() > 0)
                    <span class="absolute top-2 right-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                        {{ $pendingBoardApprovals->count() }}
                    </span>
                    @endif
                </button>
                <button wire:click="$set('activeTab', 'collection')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'collection' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Collection Efforts
                </button>
                <button wire:click="$set('activeTab', 'recovery')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'recovery' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Recovery
                </button>
                <button wire:click="$set('activeTab', 'communications')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'communications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Communications
                </button>
                <button wire:click="$set('activeTab', 'analytics')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'analytics' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Analytics
                </button>
                <button wire:click="$set('activeTab', 'audit')" 
                        class="px-6 py-3 border-b-2 font-medium text-sm 
                        {{ $activeTab === 'audit' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Audit Trail
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div>
        @if($activeTab === 'overview')
            <!-- Overview Tab Content -->
            @include('livewire.active-loan.partials.writeoff-overview')
        @elseif($activeTab === 'approvals')
            <!-- Board Approvals Tab Content -->
            @include('livewire.active-loan.writeoff-board-approvals')
        @elseif($activeTab === 'collection')
            <!-- Collection Efforts Tab Content -->
            @include('livewire.active-loan.writeoff-collection-efforts')
        @elseif($activeTab === 'recovery')
            <!-- Recovery Tracking Tab Content -->
            @include('livewire.active-loan.writeoff-recovery')
        @elseif($activeTab === 'communications')
            <!-- Member Communications Tab Content -->
            @include('livewire.active-loan.partials.writeoff-communications')
        @elseif($activeTab === 'analytics')
            <!-- Analytics & Reporting Tab Content -->
            @include('livewire.active-loan.writeoff-analytics')
        @elseif($activeTab === 'audit')
            <!-- Audit Trail Tab Content -->
            @include('livewire.active-loan.partials.writeoff-audit-trail')
        @endif
    </div>
</div>