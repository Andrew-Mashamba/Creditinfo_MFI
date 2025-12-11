<div class="bg-gray-900 text-gray-100 min-h-screen p-4" 
     x-data="{ showDetails: {}, showRaw: {} }"
     @if($autoRefresh) wire:poll.{{ $refreshInterval }}ms="refreshLogs" @endif>
    
    <!-- Header -->
    <div class="bg-gray-800 rounded-lg p-4 mb-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-cyan-400">🔍 Prompt Chain Logger</h1>
            <button wire:click="toggleAutoRefresh" 
                    class="px-4 py-2 rounded {{ $autoRefresh ? 'bg-green-600' : 'bg-gray-600' }}">
                Auto Refresh: {{ $autoRefresh ? 'ON' : 'OFF' }}
            </button>
        </div>
        
        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm mb-1">Session ID</label>
                <input type="text" wire:model.debounce.300ms="sessionId" 
                       placeholder="Filter by session..." 
                       class="w-full px-3 py-2 bg-gray-700 rounded text-white">
            </div>
            
            <div>
                <label class="block text-sm mb-1">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" 
                       placeholder="Search logs..." 
                       class="w-full px-3 py-2 bg-gray-700 rounded text-white">
            </div>
            
            <div>
                <label class="block text-sm mb-1">Step Filter</label>
                <select wire:model="filterStep" class="w-full px-3 py-2 bg-gray-700 rounded text-white">
                    <option value="">All Steps</option>
                    @for($i = 1; $i <= 21; $i++)
                        <option value="{{ $i }}">Step {{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="showOnlyErrors" class="mr-2">
                    <span class="text-sm">Errors Only</span>
                </label>
                <button wire:click="clearFilters" 
                        class="px-4 py-2 bg-red-600 rounded hover:bg-red-700">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
    
    <!-- Step Legend -->
    <div class="bg-gray-800 rounded-lg p-4 mb-4">
        <h2 class="text-lg font-bold mb-2 text-cyan-400">📍 Prompt Chain Steps</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 text-sm">
            <div>🔵 Step 1-6: AiAgentChat</div>
            <div>🔵 Step 7-9: HybridAiService</div>
            <div>🔴 Step 10-12: ContextEnhancement</div>
            <div>🟠 Step 13-17: LocalClaude</div>
            <div>🔶 Step 18-19: ClaudeCLI</div>
            <div>🔸 Step 20-21: MCPDatabase</div>
        </div>
    </div>
    
    <!-- Logs Display -->
    <div class="bg-gray-800 rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-cyan-400">📋 Prompt Chain Logs ({{ count($logs) }})</h2>
            <button wire:click="refreshLogs" 
                    class="px-4 py-2 bg-red-600 rounded hover:bg-red-700">
                Refresh Now
            </button>
        </div>
        
        @if(count($logs) > 0)
            <div class="space-y-2">
                @foreach($logs as $index => $log)
                    <div class="bg-gray-700 rounded p-3 hover:bg-gray-650 transition-colors">
                        <!-- Log Header -->
                        <div class="flex items-center justify-between cursor-pointer"
                             @click="showDetails[{{ $index }}] = !showDetails[{{ $index }}]">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $log['emoji'] }}</span>
                                <div>
                                    <span class="text-cyan-400 font-mono text-xs">
                                        {{ $log['timestamp'] }}
                                    </span>
                                    <span class="ml-2 px-2 py-1 text-xs rounded
                                        {{ $log['level'] === 'ERROR' ? 'bg-red-600' : '' }}
                                        {{ $log['level'] === 'WARNING' ? 'bg-yellow-600' : '' }}
                                        {{ $log['level'] === 'INFO' ? 'bg-red-600' : '' }}">
                                        {{ $log['level'] }}
                                    </span>
                                    <span class="ml-2 px-2 py-1 bg-purple-600 text-xs rounded">
                                        Step {{ $log['step'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-white font-semibold">{{ $log['title'] }}</div>
                                <div class="text-gray-400 text-xs">{{ $log['location'] }}</div>
                            </div>
                        </div>
                        
                        <!-- Log Details (Expandable) -->
                        <div x-show="showDetails[{{ $index }}]" 
                             x-transition 
                             class="mt-3 pt-3 border-t border-gray-600">
                            
                            @if($log['session_id'])
                                <div class="mb-2">
                                    <span class="text-gray-400">Session:</span>
                                    <span class="text-yellow-400 font-mono">{{ $log['session_id'] }}</span>
                                </div>
                            @endif
                            
                            <!-- Data Display -->
                            <div class="bg-gray-800 rounded p-2 mb-2">
                                <div class="text-sm font-mono text-gray-300">
                                    @foreach($log['data'] as $key => $value)
                                        @if(!in_array($key, ['step', 'session_id', 'location']))
                                            <div class="mb-1">
                                                <span class="text-cyan-400">{{ $key }}:</span>
                                                @if(is_array($value))
                                                    <span class="text-gray-400">{{ json_encode($value, JSON_PRETTY_PRINT) }}</span>
                                                @else
                                                    <span class="text-gray-400">{{ $value }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Raw Log Toggle -->
                            <button @click="showRaw[{{ $index }}] = !showRaw[{{ $index }}]"
                                    class="text-xs text-red-400 hover:text-red-300">
                                Toggle Raw Log
                            </button>
                            
                            <div x-show="showRaw[{{ $index }}]" class="mt-2">
                                <pre class="bg-black rounded p-2 text-xs text-green-400 overflow-x-auto">{{ $log['raw'] }}</pre>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p>No prompt chain logs found.</p>
                <p class="text-sm mt-2">Start a conversation in the AI Agent Chat to see logs appear here.</p>
            </div>
        @endif
    </div>
    
    <!-- Session Flow Modal -->
    @if($sessionId)
        <div class="mt-4 bg-gray-800 rounded-lg p-4">
            <h2 class="text-lg font-bold mb-4 text-cyan-400">🔄 Session Flow: {{ $sessionId }}</h2>
            <div class="space-y-2">
                @foreach($this->getPromptFlow($sessionId) as $flowLog)
                    <div class="flex items-center gap-3">
                        <span class="text-xl">{{ $flowLog['emoji'] }}</span>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-purple-600 text-xs rounded">
                                    Step {{ $flowLog['step'] }}
                                </span>
                                <span class="text-white">{{ $flowLog['title'] }}</span>
                                <span class="text-gray-500 text-xs">{{ $flowLog['location'] }}</span>
                            </div>
                        </div>
                        <span class="text-gray-400 text-xs">{{ $flowLog['timestamp'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Optional: Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'r' && e.ctrlKey) {
            e.preventDefault();
            @this.refreshLogs();
        }
        if (e.key === 'c' && e.ctrlKey && e.shiftKey) {
            e.preventDefault();
            @this.clearFilters();
        }
    });
</script>
@endpush