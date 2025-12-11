<div class="h-screen flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-900 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">AI Assistant</h1>
                <p class="text-xs text-gray-500">SACCOS Intelligence System (Direct Mode)</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <button wire:click="newConversation" class="px-3 py-1.5 text-sm bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                New Chat
            </button>
            <button wire:click="clearChat" class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Clear
            </button>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto p-4" id="messages-container">
        @if(empty($messages))
        <!-- Welcome Screen -->
        <div class="max-w-2xl mx-auto text-center py-12">
            <div class="w-20 h-20 bg-blue-900 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to AI Assistant</h2>
            <p class="text-gray-600 mb-8">How can I help you today?</p>
            
            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3 max-w-lg mx-auto">
                <button wire:click="sendQuickMessage('Show me account summary')" class="p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow">
                    <div class="text-sm font-medium text-gray-900">Account Summary</div>
                    <div class="text-xs text-gray-500 mt-1">View account overview</div>
                </button>
                
                <button wire:click="sendQuickMessage('Generate monthly report')" class="p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow">
                    <div class="text-sm font-medium text-gray-900">Generate Report</div>
                    <div class="text-xs text-gray-500 mt-1">Create monthly reports</div>
                </button>
                
                <button wire:click="sendQuickMessage('Show members list')" class="p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow">
                    <div class="text-sm font-medium text-gray-900">Members</div>
                    <div class="text-xs text-gray-500 mt-1">View member list</div>
                </button>
                
                <button wire:click="sendQuickMessage('Show loan portfolio')" class="p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow">
                    <div class="text-sm font-medium text-gray-900">Loans</div>
                    <div class="text-xs text-gray-500 mt-1">View loan portfolio</div>
                </button>
            </div>
        </div>
        @else
        <!-- Messages List -->
        <div class="space-y-4 max-w-4xl mx-auto">
            @foreach($messages as $message)
                @if($message['sender'] === 'user')
                <div class="flex justify-end">
                    <div class="max-w-2xl">
                        <div class="bg-blue-900 text-white rounded-lg px-4 py-2">
                            {{-- SECURITY: User messages are escaped to prevent XSS --}}
                            <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 text-right">
                            {{ \Carbon\Carbon::parse($message['timestamp'])->format('H:i') }}
                        </p>
                    </div>
                </div>
                @else
                <div class="flex justify-start">
                    <div class="max-w-2xl">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="bg-white rounded-lg px-4 py-2 shadow-sm border {{ $message['isError'] ?? false ? 'border-red-300' : 'border-gray-200' }}">
                                    {{-- SECURITY: AI responses are escaped to prevent XSS attacks --}}
                                    {{-- If formatted content is needed, implement a proper HTML sanitizer --}}
                                    <div class="text-sm whitespace-pre-wrap {{ $message['isError'] ?? false ? 'text-red-600' : 'text-gray-800' }}">
                                        {!! nl2br(e($message['content'])) !!}
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($message['timestamp'])->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
            
            <!-- Loading Message -->
            @if($isLoading)
            <div class="flex justify-start">
                <div class="max-w-2xl">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-900 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="bg-white rounded-lg px-4 py-3 shadow-sm border border-gray-200">
                                <div class="flex space-x-2">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t px-4 py-4">
        <form wire:submit.prevent="processDirectMessage" class="max-w-4xl mx-auto">
            <div class="flex space-x-3">
                <input type="text" 
                       wire:model="message"
                       wire:keydown.enter.prevent="processDirectMessage"
                       placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                       @if($isLoading) disabled @endif>
                
                <button type="submit" 
                        class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if($isLoading || empty($message)) disabled @endif>
                    Send
                </button>
            </div>
        </form>
        
        <!-- Error Display -->
        @if($error)
        <div class="max-w-4xl mx-auto mt-2">
            <div class="text-sm text-red-600">{{ $error }}</div>
        </div>
        @endif
    </div>
    
    <!-- Auto-scroll script -->
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function () {
            // Auto-scroll to bottom when messages are updated
            Livewire.hook('message.processed', (message, component) => {
                if (component.fingerprint.name === 'ai-agent.ai-agent-chat') {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        setTimeout(() => {
                            container.scrollTop = container.scrollHeight;
                        }, 100);
                    }
                }
            });
        });
        
        // Focus on input after page load
        window.addEventListener('load', function() {
            const input = document.querySelector('input[wire\\:model="message"]');
            if (input) {
                input.focus();
            }
        });
    </script>
</div>