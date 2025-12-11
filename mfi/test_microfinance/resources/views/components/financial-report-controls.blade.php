{{-- 
    Financial Report Controls Component
    Reusable controls for all financial reports
--}}

<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Report Period -->
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-600 mb-1">Report Period</label>
            <select wire:model.live="reportPeriod" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="annually">Annually</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        
        <!-- Start Date -->
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-600 mb-1">Start Date</label>
            <input type="date" wire:model.live="startDate" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
        
        <!-- End Date -->
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-600 mb-1">End Date</label>
            <input type="date" wire:model.live="endDate" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
        
        <!-- Currency -->
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-600 mb-1">Currency</label>
            <select wire:model.live="currency" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="TZS">TZS</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
            </select>
        </div>
        
        <!-- View Format -->
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-600 mb-1">View Format</label>
            <select wire:model.live="viewFormat" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="detailed">Detailed</option>
                <option value="summary">Summary</option>
                <option value="comparative">Comparative</option>
            </select>
        </div>
        
        <!-- Generate Button -->
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-600 mb-1">Action</label>
            <button wire:click="generateReport" 
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                <svg wire:loading.remove class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <svg wire:loading class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove>Generate</span>
                <span wire:loading>Loading...</span>
            </button>
        </div>
    </div>
</div> 