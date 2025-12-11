<div>
    <!-- Auto-refresh script -->
    @if($autoRefresh && $isRunning)
    <script nonce="{{ csp_nonce() }}">
        setTimeout(function() {
            @this.call('loadActivities');
        }, {{ $refreshInterval * 1000 }});
    </script>
    @endif

    <style nonce="{{ csp_nonce() }}">
        .main-color {
            color: #2D3D88;
        }
        .main-color-hover:hover {
            color: white;
        }
        .secondary-color {
            color: red;
        }
        .secondary-color-hover:hover {
            color: white;
        }
        .main-color-bg {
            background-color: #2D3D88;
        }
        .main-color-bg-hover:hover {
            background-color: #2D3D88;
            color: white;
        }
        .icon-hover:hover {
            color: #2D3D88;
        }
        .box-button {
            color: #2D3D88;
        }
        .box-button:hover {
            background-color: #2D3D88;
            color: white;
        }
        .box-button:hover .icon-svg {
            stroke: white !important;
        }
        .icon-svg {
            fill: #2D3D88;
        }
        .icon-color {
            color: #2D3D88;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin 2s linear infinite;
        }
    </style>

    <!-- Component -->
    <div class="w-full flex items-center justify-center h-full bg-gray-100 p-2">
        <section class="w-full p-6 rounded-lg shadow-gray-300 bg-white">
            <!-- Header with status info -->
            <header class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 shrink-0 w-6 h-6 text-gray-500" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M4 19l16 0"></path>
                        <path d="M4 15l4 -6l4 2l4 -5l4 4"></path>
                    </svg>
                    <h3 class="font-medium text-lg">End of Day Processes</h3>
                    @if($isRunning)
                        <span class="ml-3 px-2 py-1 rounded-lg bg-red-50 text-red-700 text-xs flex items-center">
                            <svg class="animate-spin-slow h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Running
                        </span>
                    @endif
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Auto-refresh toggle -->
                    <div class="flex items-center">
                        <label class="text-sm text-gray-600 mr-2">Auto-refresh:</label>
                        <button wire:click="toggleAutoRefresh" class="relative inline-flex h-6 w-11 items-center rounded-full {{ $autoRefresh ? 'bg-red-600' : 'bg-gray-200' }} transition-colors">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $autoRefresh ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                    
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 shrink-0 w-6 h-6 text-gray-500" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                        <path d="M12 9h.01"></path>
                        <path d="M11 12h1v4h1"></path>
                    </svg>
                </div>
            </header>

            <!-- Status Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Last Run:</span>
                        <span class="font-medium ml-2">{{ $lastRunDate ?? 'Never' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Next Run:</span>
                        <span class="font-medium ml-2">{{ $nextRunTime }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Overall Progress:</span>
                        <span class="font-medium ml-2">{{ $overallProgress }}%</span>
                    </div>
                </div>
                
                <!-- Overall Progress Bar -->
                <div class="mt-3">
                    <div class="overflow-hidden bg-gray-200 h-2 rounded-full w-full">
                        <span class="h-full {{ $this->getProgressBarClass($overallProgress) }} block rounded-full transition-all duration-500" style="width: {{ $overallProgress }}%"></span>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Activities List -->
            <section class="py-4 grid grid-cols-2 gap-x-6">
                @foreach($activities as $activity)
                <div class="flex items-center py-3 {{ !$loop->first && $loop->index % 2 == 0 ? 'border-t border-gray-100' : '' }}">
                    <span class="w-8 h-8 shrink-0 mr-4 rounded-full bg-red-50 flex items-center justify-center">
                        @if($activity['status'] == 'running')
                            <svg class="animate-spin-slow h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" width="24" height="24" stroke-linecap="round" stroke-linejoin="round" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        @endif
                    </span>
                    
                    <div class="space-y-3 flex-1">
                        <div class="flex items-center">
                            <h4 class="font-medium text-sm mr-auto text-gray-700 flex items-center">
                                {{ $activity['name'] }}
                                
                                <!-- Status indicator -->
                                <span class="ml-2 text-xs {{ $this->getStatusIcon($activity['status']) }}">
                                    {{ $this->getStatusIcon($activity['status']) }}
                                </span>
                                
                                <!-- Show error icon if failed -->
                                @if($activity['status'] == 'failed' && $activity['lastError'])
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 shrink-0 w-4 h-4 text-red-500" viewBox="0 0 20 20" fill="currentColor" title="{{ $activity['lastError'] }}">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </h4>
                            
                            <span class="px-2 py-1 rounded-lg text-xs {{ $this->getStatusBadgeClass($activity['status']) }}">
                                @if($activity['status'] == 'completed')
                                    {{ $activity['progress'] }}%
                                @elseif($activity['status'] == 'running')
                                    {{ $activity['progress'] }}%
                                @else
                                    {{ ucfirst($activity['status']) }}
                                @endif
                            </span>
                        </div>
                        
                        <!-- Progress bar -->
                        <div class="overflow-hidden bg-red-50 h-1.5 rounded-full w-full">
                            <span class="h-full {{ $this->getProgressBarClass($activity['progress']) }} block rounded-full transition-all duration-500" 
                                  style="width: {{ $activity['progress'] }}%"></span>
                        </div>
                        
                        <!-- Additional info for running/completed tasks -->
                        @if($activity['status'] == 'running' || $activity['status'] == 'completed')
                            <div class="text-xs text-gray-500">
                                @if($activity['totalRecords'] > 0)
                                    <span>{{ $activity['processedRecords'] }}/{{ $activity['totalRecords'] }} records</span>
                                @endif
                                @if($activity['executionTime'])
                                    <span class="ml-2">Time: {{ $activity['executionTime'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </section>

            <!-- Footer with action button -->
            <footer class="border-t border-gray-100 pt-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        @if($isRunning)
                            <span class="flex items-center">
                                <svg class="animate-spin-slow h-4 w-4 mr-2 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing daily activities...
                            </span>
                        @else
                            Click "Run Now" to manually trigger daily activities
                        @endif
                    </div>
                    
                    <div class="flex gap-2">
                        <button wire:click="viewLogs"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            VIEW LOGS
                        </button>
                        
                        <button wire:click="runManually" 
                                {{ $isRunning ? 'disabled' : '' }}
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white main-color-bg rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 {{ $isRunning ? 'opacity-50 cursor-not-allowed' : '' }}">
                            @if($isRunning)
                                <svg class="animate-spin-slow h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                RUNNING...
                            @else
                                RUN NOW
                            @endif
                        </button>
                    </div>
                </div>
            </footer>
        </section>
    </div>

    <!-- Log Viewer Modal -->
    @if($showLogs)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-medium text-gray-900">End of Day Activity Logs</h3>
                    <button wire:click="closeLogs" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Log Controls -->
                <div class="flex gap-4 mt-4 mb-4">
                    <div>
                        <label class="text-sm text-gray-600">Date:</label>
                        <input type="date" wire:model="selectedDate" wire:change="loadLogs" 
                               class="ml-2 px-3 py-1 border border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">View:</label>
                        <select wire:model="logType" wire:change="loadLogs" 
                                class="ml-2 px-3 py-1 border border-gray-300 rounded-md text-sm">
                            <option value="summary">Summary</option>
                            <option value="full">Full Log</option>
                            <option value="errors">Errors Only</option>
                        </select>
                    </div>
                    <button wire:click="loadLogs" 
                            class="px-4 py-1 bg-red-500 text-white text-sm rounded-md hover:bg-red-600">
                        Refresh
                    </button>
                    <button wire:click="downloadLogs" 
                            class="px-4 py-1 bg-green-500 text-white text-sm rounded-md hover:bg-green-600">
                        Download
                    </button>
                </div>

                <!-- Log Content -->
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-auto" style="max-height: 500px;">
                    <pre class="text-xs font-mono whitespace-pre-wrap">{{ $logContent }}</pre>
                </div>

                <!-- Using Command Line -->
                <div class="mt-4 p-3 bg-red-50 rounded-lg">
                    <p class="text-sm text-red-800">
                        <strong>Tip:</strong> You can also view logs using the command line:
                        <code class="bg-red-100 px-1 py-0.5 rounded">php artisan eod:logs --summary</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>