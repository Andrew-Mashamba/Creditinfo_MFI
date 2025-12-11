<div>
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Manage and monitor Laravel scheduled tasks. View execution history, track errors, and manually run tasks.</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- View Mode Toggle --}}
                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                    <button wire:click="setViewMode('table')" class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'table' ? 'bg-red-600 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Table
                    </button>
                    <button wire:click="setViewMode('calendar')" class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'calendar' ? 'bg-red-600 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar
                    </button>
                </div>
                <button wire:click="loadTasks" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
        @if($lastRefresh)
        <p class="text-xs text-gray-500 mt-2">Last refreshed: {{ $lastRefresh }}</p>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex">
            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-6 gap-4 mb-6">
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
            <div class="flex items-center">
                <div class="p-2 bg-red-500 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-600">Total Tasks</p>
                    <p class="text-2xl font-bold text-red-900">{{ $totalTaskCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-4 border border-emerald-200 cursor-pointer hover:shadow-md transition-shadow" wire:click="$set('enabledFilter', 'enabled')">
            <div class="flex items-center">
                <div class="p-2 bg-emerald-500 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-emerald-600">Enabled</p>
                    <p class="text-2xl font-bold text-emerald-900">{{ $enabledTaskCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border border-slate-200 cursor-pointer hover:shadow-md transition-shadow" wire:click="$set('enabledFilter', 'disabled')">
            <div class="flex items-center">
                <div class="p-2 bg-slate-500 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-slate-600">Disabled</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $disabledTaskCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-500 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-600">Successful</p>
                    <p class="text-2xl font-bold text-green-900">{{ $stats['success'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
            <div class="flex items-center">
                <div class="p-2 bg-red-500 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-600">Failed</p>
                    <p class="text-2xl font-bold text-red-900">{{ $stats['failed'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
            <div class="flex items-center">
                <div class="p-2 bg-orange-500 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-orange-600">Categories</p>
                    <p class="text-2xl font-bold text-orange-900">{{ collect($tasks)->pluck('category')->unique()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search tasks..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select wire:model="categoryFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Frequency</label>
                <select wire:model="frequencyFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                    <option value="">All Frequencies</option>
                    @foreach($frequencies as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select wire:model="enabledFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                    <option value="">All Status</option>
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
            <div class="pt-5">
                <button wire:click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Clear Filters
                </button>
            </div>
        </div>
        @if($searchTerm || $categoryFilter || $frequencyFilter || $enabledFilter)
        <div class="mt-3 flex items-center text-sm text-gray-600">
            <span>Showing {{ count($tasks) }} of {{ $totalTaskCount }} tasks</span>
            @if($enabledFilter === 'disabled')
            <span class="ml-2 px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full text-xs">Disabled only</span>
            @elseif($enabledFilter === 'enabled')
            <span class="ml-2 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs">Enabled only</span>
            @endif
        </div>
        @endif
    </div>

    @if($viewMode === 'table')
    {{-- Tasks Table View --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Command
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Schedule
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Run
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Next Due
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tasks as $index => $task)
                    <tr class="hover:bg-gray-50 transition-colors duration-150 {{ !($task['is_enabled'] ?? true) ? 'opacity-60 bg-gray-50' : '' }}" wire:key="task-{{ $task['id'] ?? $index }}">
                        <td class="px-4 py-3 text-center">
                            {{-- Toggle Switch --}}
                            <button
                                wire:click="toggleTask('{{ $task['id'] }}', '{{ $task['command'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="toggleTask('{{ $task['id'] }}', '{{ $task['command'] }}')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 {{ ($task['is_enabled'] ?? true) ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                role="switch"
                                aria-checked="{{ ($task['is_enabled'] ?? true) ? 'true' : 'false' }}"
                                title="{{ ($task['is_enabled'] ?? true) ? 'Click to disable task' : 'Click to enable task' }}"
                            >
                                <span class="sr-only">Toggle task</span>
                                <span
                                    aria-hidden="true"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    style="{{ ($task['is_enabled'] ?? true) ? 'transform: translateX(20px);' : 'transform: translateX(0);' }}"
                                ></span>
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900 font-mono {{ !($task['is_enabled'] ?? true) ? 'line-through text-gray-500' : '' }}">{{ $task['command'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">{{ $task['description'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getCategoryColor($task['category']) }}">
                                {{ $task['category'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm text-gray-900">{{ $task['schedule'] }}</span>
                                <span class="text-xs text-gray-400 font-mono mt-1">{{ $task['cron'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($task['lastRun'])
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    @if($task['lastRun']['status'] === 'success')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Success
                                    </span>
                                    @elseif($task['lastRun']['status'] === 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Failed
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Running
                                    </span>
                                    @endif
                                    <span class="text-xs text-gray-500 ml-2">{{ $task['lastRun']['duration'] }}</span>
                                </div>
                                <span class="text-xs text-gray-500 mt-1">{{ $task['lastRun']['timeAgo'] }}</span>
                                @if($task['lastRun']['hasError'])
                                <span class="text-xs text-red-600 mt-1 truncate max-w-xs" title="{{ $task['lastRun']['errorMessage'] }}">
                                    {{ Str::limit($task['lastRun']['errorMessage'], 50) }}
                                </span>
                                @endif
                            </div>
                            @else
                            <span class="text-xs text-gray-400 italic">Never run</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm text-gray-600">{{ $task['nextDueFormatted'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button
                                    wire:click="runTaskById('{{ $task['id'] ?? $index }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="runTaskById('{{ $task['id'] ?? $index }}')"
                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                >
                                    <span wire:loading.remove wire:target="runTaskById('{{ $task['id'] ?? $index }}')">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        </svg>
                                        Run
                                    </span>
                                    <span wire:loading wire:target="runTaskById('{{ $task['id'] ?? $index }}')">
                                        <svg class="animate-spin w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <button
                                    wire:click="viewHistory('{{ $task['command'] }}')"
                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                >
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    History
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    {{-- Calendar View --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Calendar Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">{{ $monthName }}</h3>
            <div class="flex items-center space-x-2">
                <button wire:click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button wire:click="goToToday" class="px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    Today
                </button>
                <button wire:click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
        {{-- Calendar Grid --}}
        <div class="p-4">
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 gap-1 mb-2">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-center text-xs font-medium text-gray-500 py-2">{{ $day }}</div>
                @endforeach
            </div>
            {{-- Calendar Days --}}
            <div class="grid grid-cols-7 gap-1">
                @foreach($calendarDays as $dayData)
                    @if($dayData === null)
                    <div class="h-24 bg-gray-50 rounded-lg"></div>
                    @else
                    <div
                        wire:click="selectDate('{{ $dayData['date'] }}')"
                        class="h-24 p-2 rounded-lg border cursor-pointer transition-all duration-200 hover:border-red-300 hover:shadow-sm {{ $dayData['isToday'] ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-white' }}"
                    >
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium {{ $dayData['isToday'] ? 'text-red-600' : 'text-gray-900' }}">{{ $dayData['day'] }}</span>
                            @if($dayData['data'])
                            <div class="flex space-x-1">
                                @if(($dayData['data']['success'] ?? 0) > 0)
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                @endif
                                @if(($dayData['data']['failed'] ?? 0) > 0)
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @if($dayData['data'])
                        <div class="mt-1">
                            <div class="text-xs text-gray-500">{{ $dayData['data']['total'] }} task{{ $dayData['data']['total'] > 1 ? 's' : '' }}</div>
                            @if(($dayData['data']['failed'] ?? 0) > 0)
                            <div class="text-xs text-red-600">{{ $dayData['data']['failed'] }} failed</div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        {{-- Legend --}}
        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex items-center space-x-6 text-xs">
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                <span class="text-gray-600">Successful runs</span>
            </div>
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                <span class="text-gray-600">Failed runs</span>
            </div>
            <div class="flex items-center">
                <span class="w-3 h-3 rounded border-2 border-red-500 mr-2"></span>
                <span class="text-gray-600">Today</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Output Modal --}}
    @if($showOutput)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Task Output
                        </h3>
                        <button wire:click="closeOutput" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <pre class="text-green-400 text-sm font-mono whitespace-pre-wrap">{{ $taskOutput ?: 'No output' }}</pre>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeOutput" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- History Modal --}}
    @if($showHistory)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Execution History: <span class="font-mono text-red-600">{{ $selectedTask }}</span>
                        </h3>
                        <button wire:click="closeHistory" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        @if(count($taskHistory) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Trigger</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($taskHistory as $history)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($history['started_at'])->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($history['status'] === 'success')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Success</span>
                                        @elseif($history['status'] === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Failed</span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Running</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600">
                                        {{ $history['duration_seconds'] ? $history['duration_seconds'] . 's' : '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $history['trigger_type'] === 'manual' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($history['trigger_type']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-red-600 max-w-xs truncate" title="{{ $history['error_message'] }}">
                                        {{ $history['error_message'] ? Str::limit($history['error_message'], 40) : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2">No execution history found</p>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeHistory" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Date Tasks Modal --}}
    @if($showDateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Tasks on {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                        </h3>
                        <button wire:click="closeDateModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        @if(count($selectedDateTasks) > 0)
                        <div class="space-y-3">
                            @foreach($selectedDateTasks as $task)
                            <div class="p-3 rounded-lg border {{ $task['status'] === 'success' ? 'border-green-200 bg-green-50' : ($task['status'] === 'failed' ? 'border-red-200 bg-red-50' : 'border-red-200 bg-red-50') }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-sm font-medium text-gray-900">{{ $task['command'] }}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($task['started_at'])->format('H:i:s') }}</span>
                                        @if($task['status'] === 'success')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Success</span>
                                        @elseif($task['status'] === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Failed</span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Running</span>
                                        @endif
                                    </div>
                                </div>
                                @if($task['error_message'])
                                <p class="mt-2 text-sm text-red-600">{{ $task['error_message'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2">No tasks executed on this date</p>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeDateModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Info Section --}}
    <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">About Scheduled Tasks</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Tasks are executed automatically by the Laravel scheduler via <code class="bg-red-100 px-1 rounded">saccos-scheduler.service</code></li>
                        <li>Click "Run" to execute a task immediately and log the result</li>
                        <li>Click "History" to view past executions for a specific task</li>
                        <li>Switch to Calendar view to see task executions by date</li>
                        <li>Statistics show task runs from the last 7 days</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
