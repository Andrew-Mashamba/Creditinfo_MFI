<div class="h-screen flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">AI Assistant</h1>
                <p class="text-xs text-gray-500">MFI Intelligence System</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <button id="new-chat-btn" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-800">
                New Chat
            </button>
            <button id="clear-chat-btn" class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Clear
            </button>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto p-4" id="messages-container">
        <!-- Welcome Screen -->
        <div id="welcome-screen" class="max-w-2xl mx-auto text-center py-12">
            <div class="w-20 h-20 bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to AI Assistant</h2>
            <p class="text-gray-600 mb-8">How can I help you today?</p>
            
            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3 max-w-lg mx-auto">
                <button class="quick-action p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-red-300 hover:shadow" 
                        data-message="Show me account summary">
                    <div class="text-sm font-medium text-gray-900">Account Summary</div>
                    <div class="text-xs text-gray-500 mt-1">View account overview</div>
                </button>
                
                <button class="quick-action p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-red-300 hover:shadow"
                        data-message="Generate monthly report">
                    <div class="text-sm font-medium text-gray-900">Generate Report</div>
                    <div class="text-xs text-gray-500 mt-1">Create monthly reports</div>
                </button>
                
                <button class="quick-action p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-red-300 hover:shadow"
                        data-message="Show members list">
                    <div class="text-sm font-medium text-gray-900">Members</div>
                    <div class="text-xs text-gray-500 mt-1">View member list</div>
                </button>
                
                <button class="quick-action p-3 text-left bg-white rounded-lg border border-gray-200 hover:border-red-300 hover:shadow"
                        data-message="Show loan portfolio">
                    <div class="text-sm font-medium text-gray-900">Loans</div>
                    <div class="text-xs text-gray-500 mt-1">View loan portfolio</div>
                </button>
            </div>
        </div>

        <!-- Messages will be appended here -->
        <div id="messages-list" class="space-y-4 max-w-4xl mx-auto hidden"></div>
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t px-4 py-4">
        <form id="message-form" class="max-w-4xl mx-auto">
            @csrf
            <div class="flex space-x-3">
                <input type="text" 
                       id="message-input"
                       placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                
                <button type="submit" 
                        id="send-btn"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    Send
                </button>
            </div>
        </form>
        
        <!-- Error Display -->
        <div id="error-display" class="max-w-4xl mx-auto mt-2 hidden">
            <div class="text-sm text-red-600"></div>
        </div>
    </div>
    
    <script nonce="{{ csp_nonce() }}">
    (function() {
        // Encapsulate in IIFE to avoid global namespace pollution
        class AIAgentChat {
            constructor() {
                this.messages = [];
                this.sessionId = this.generateSessionId();
                this.isLoading = false;
                this.eventSource = null;
                // Get CSRF token from multiple sources
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                document.querySelector('input[name="_token"]')?.value || 
                                document.querySelector('[name="csrf-token"]')?.getAttribute('content') || '';
                
                console.log('=== AI AGENT CHAT INITIALIZED ===');
                console.log('Session ID:', this.sessionId);
                console.log('CSRF Token:', this.csrfToken ? 'Found' : 'NOT FOUND');
                console.log('Auth Check:', document.cookie.includes('saccos_core_system_session') ? 'Authenticated' : 'Not Authenticated');
                
                // DOM elements
                this.messagesContainer = document.getElementById('messages-container');
                this.messagesList = document.getElementById('messages-list');
                this.welcomeScreen = document.getElementById('welcome-screen');
                this.messageInput = document.getElementById('message-input');
                this.messageForm = document.getElementById('message-form');
                this.sendBtn = document.getElementById('send-btn');
                this.newChatBtn = document.getElementById('new-chat-btn');
                this.clearChatBtn = document.getElementById('clear-chat-btn');
                this.errorDisplay = document.getElementById('error-display');
                
                this.init();
            }
            
            init() {
                // Prevent Livewire from intercepting form submission
                if (this.messageForm) {
                    // Remove any wire:submit attributes
                    this.messageForm.removeAttribute('wire:submit');
                    this.messageForm.removeAttribute('wire:submit.prevent');
                    
                    // Add our own submit handler
                    this.messageForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.sendMessage();
                        return false;
                    });
                }
                
                // Button event listeners
                if (this.newChatBtn) {
                    this.newChatBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.newConversation();
                    });
                }
                
                if (this.clearChatBtn) {
                    this.clearChatBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.clearChat();
                    });
                }
                
                // Quick actions
                document.querySelectorAll('.quick-action').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const message = btn.getAttribute('data-message');
                        this.messageInput.value = message;
                        this.sendMessage();
                    });
                });
                
                // Enter key to send
                if (this.messageInput) {
                    this.messageInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            this.sendMessage();
                        }
                    });
                }
                
                // Load existing conversation from localStorage
                this.loadConversation();
            }
            
            generateSessionId() {
                return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            }
            
            sendMessage() {
                const message = this.messageInput.value.trim();
                if (!message || this.isLoading) return;
                
                // Hide welcome screen if visible
                if (this.welcomeScreen && !this.welcomeScreen.classList.contains('hidden')) {
                    this.welcomeScreen.classList.add('hidden');
                    this.messagesList.classList.remove('hidden');
                }
                
                // Add user message
                this.addMessage('user', message);
                
                // Clear input
                this.messageInput.value = '';
                
                // Show loading
                this.setLoading(true);
                
                // Send to API
                this.sendToAPI(message);
            }
            
            async sendToAPI(message) {
                console.log('=== USING LIVEWIRE DIRECT METHOD ===');
                console.log('Message:', message);
                console.log('Session ID:', this.sessionId);
                console.log('Timestamp:', new Date().toISOString());
                
                try {
                    // Call Livewire component method directly
                    console.log('Calling Livewire component processDirectMessage...');
                    
                    // Add a temporary loading message
                    const loadingId = this.addMessage('assistant', '<div class="text-gray-500">Processing your request...</div>');
                    
                    // Use Livewire to process the message
                    @this.call('processDirectMessage', message).then(() => {
                        console.log('Livewire call completed');
                        // Remove the loading message
                        const loadingMsg = document.querySelector(`[data-message-id="${loadingId}"]`);
                        if (loadingMsg) {
                            loadingMsg.remove();
                        }
                    }).catch((error) => {
                        console.error('Livewire call failed:', error);
                        // Update loading message with error
                        const loadingMsg = document.querySelector(`[data-message-id="${loadingId}"]`);
                        if (loadingMsg) {
                            loadingMsg.querySelector('.message-content').innerHTML = 
                                `<div class="text-red-600">Error: Failed to process message. Please try again.</div>`;
                        }
                    });
                    
                } catch (error) {
                    console.error('=== LIVEWIRE ERROR ===');
                    console.error('Error Type:', error.name);
                    console.error('Error Message:', error.message);
                    console.error('Error Stack:', error.stack);
                    
                    // Show error
                    const errorMessage = `<div class="text-red-600">Error: ${error.message || 'Failed to process message.'}</div>`;
                    this.addMessage('assistant', errorMessage, true);
                } finally {
                    console.log('=== LIVEWIRE CALL END ===');
                    this.setLoading(false);
                }
            }
            
            async tryStreamingAPI(message) {
                console.log('=== TRY STREAMING API START ===');
                
                return new Promise(async (resolve, reject) => {
                    try {
                        // First send the message to be processed
                        console.log('1. Sending POST to /ai/process');
                        console.log('   Message:', message);
                        console.log('   SessionId:', this.sessionId);
                        
                        const response = await fetch('/ai/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                message: message,
                                sessionId: this.sessionId
                            })
                        });
                        
                        console.log('2. POST Response Status:', response.status);
                        console.log('   Response OK:', response.ok);
                        
                        if (!response.ok) {
                            console.error('   POST Failed! Status:', response.status);
                            console.error('   Status Text:', response.statusText);
                            throw new Error(`Failed to process message: ${response.status} ${response.statusText}`);
                        }
                        
                        const data = await response.json();
                        console.log('3. POST Response Data:', data);
                        
                        if (!data.success) {
                            console.error('   Processing failed:', data.error);
                            throw new Error(data.error || 'Processing failed');
                        }
                        
                        // Add empty assistant message that will be updated via streaming
                        console.log('4. Adding empty assistant message for streaming');
                        this.addMessage('assistant', '');
                        
                        // Initialize streaming connection
                        // EventSource doesn't support custom headers, so we need to pass auth via URL
                        const streamUrl = `/ai/stream/${this.sessionId}?_token=${encodeURIComponent(this.csrfToken)}`;
                        console.log('5. Opening EventSource to:', streamUrl);
                        console.log('   CSRF Token:', this.csrfToken ? 'Present' : 'Missing');
                        
                        // EventSource with credentials
                        this.eventSource = new EventSource(streamUrl, {
                            withCredentials: true
                        });
                        
                        let fullResponse = '';
                        let hasReceivedContent = false;
                        let messageCount = 0;
                        
                        // Add connection opened handler
                        this.eventSource.onopen = (event) => {
                            console.log('6. EventSource Connection OPENED');
                            console.log('   ReadyState:', this.eventSource.readyState);
                        };
                        
                        this.eventSource.addEventListener('message', (event) => {
                            messageCount++;
                            console.log(`7. SSE Message #${messageCount} received`);
                            console.log('   Raw Data:', event.data);
                            
                            try {
                                const data = JSON.parse(event.data);
                                console.log('   Parsed Data:', data);
                                
                                if (data.chunk) {
                                    console.log('   Chunk Length:', data.chunk.length);
                                    fullResponse += data.chunk;
                                    hasReceivedContent = true;
                                    console.log('   Total Response Length:', fullResponse.length);
                                    // Update the last AI message with streaming content
                                    this.updateLastAssistantMessage(fullResponse);
                                } else {
                                    console.log('   No chunk in data');
                                }
                            } catch (e) {
                                console.error('   Error parsing SSE data:', e);
                                console.error('   Raw event.data:', event.data);
                            }
                        });
                        
                        this.eventSource.addEventListener('complete', () => {
                            console.log('8. SSE Complete Event Received');
                            console.log('   Has Received Content:', hasReceivedContent);
                            console.log('   Final Response Length:', fullResponse.length);
                            
                            this.eventSource.close();
                            this.eventSource = null;
                            
                            // If no content received, show a default message
                            if (!hasReceivedContent) {
                                console.log('   No content received, showing default message');
                                this.updateLastAssistantMessage('<div><p>I received your message but couldn\'t generate a response. Please try again.</p></div>');
                            }
                            
                            this.setLoading(false);
                            console.log('=== STREAMING COMPLETED ===');
                            resolve();
                        });
                        
                        this.eventSource.addEventListener('timeout', () => {
                            console.log('8. SSE Timeout Event Received');
                            this.eventSource.close();
                            this.eventSource = null;
                            this.updateLastAssistantMessage('<div class="text-orange-600">Response timed out. Please try again.</div>');
                            this.setLoading(false);
                            console.log('=== STREAMING TIMEOUT ===');
                            resolve();
                        });
                        
                        this.eventSource.addEventListener('error', (error) => {
                            console.error('8. SSE Error Event:', error);
                            console.error('   EventSource ReadyState:', this.eventSource ? this.eventSource.readyState : 'null');
                            console.error('   Error Type:', error.type);
                            console.error('   Has Received Content:', hasReceivedContent);
                            
                            if (this.eventSource) {
                                // Check readyState: 0=CONNECTING, 1=OPEN, 2=CLOSED
                                if (this.eventSource.readyState === 2) {
                                    console.error('   Connection was CLOSED');
                                } else if (this.eventSource.readyState === 0) {
                                    console.error('   Still CONNECTING (auth issue?)');
                                }
                                
                                this.eventSource.close();
                                this.eventSource = null;
                            }
                            
                            // Remove the empty assistant message
                            if (!hasReceivedContent && this.messages.length > 0) {
                                const lastMessage = this.messages[this.messages.length - 1];
                                if (lastMessage.sender === 'assistant' && lastMessage.content === '') {
                                    console.log('   Removing empty assistant message');
                                    this.messages.pop();
                                    const lastElement = this.messagesList.lastElementChild;
                                    if (lastElement) {
                                        lastElement.remove();
                                    }
                                }
                            }
                            
                            console.error('=== STREAMING ERROR END ===');
                            reject(new Error('Streaming connection failed'));
                        });
                        
                    } catch (error) {
                        console.error('=== TRY STREAMING API ERROR ===');
                        console.error('Error:', error);
                        reject(error);
                    }
                });
            }
            
            formatResponse(response) {
                // Handle different response types
                if (typeof response === 'object' && response !== null) {
                    // If it's still an object at this point, stringify it nicely
                    return `<div class="prose"><pre class="bg-gray-100 p-2 rounded text-sm">${JSON.stringify(response, null, 2)}</pre></div>`;
                }
                
                // Format the response if it's plain text
                if (typeof response === 'string') {
                    // Check if it's already HTML
                    if (response.includes('<div') || response.includes('<p') || response.includes('<table')) {
                        return response;
                    }
                    // Check if it's JSON string
                    if (response.startsWith('{') || response.startsWith('[')) {
                        try {
                            const parsed = JSON.parse(response);
                            return `<div class="prose"><pre class="bg-gray-100 p-2 rounded text-sm">${JSON.stringify(parsed, null, 2)}</pre></div>`;
                        } catch (e) {
                            // Not valid JSON, treat as plain text
                        }
                    }
                    // Plain text - make it look nice
                    return `<div class="prose">${response.replace(/\n/g, '<br>')}</div>`;
                }
                
                return `<div class="prose">${String(response)}</div>`;
            }
            
            addMessage(sender, content, isError = false) {
                const message = {
                    id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                    sender,
                    content,
                    timestamp: new Date(),
                    isError
                };
                
                this.messages.push(message);
                this.renderMessage(message);
                this.saveConversation();
                this.scrollToBottom();
                
                // Return the message ID so we can track it
                return message.id;
            }
            
            updateLastAssistantMessage(content) {
                const lastMessage = this.messages[this.messages.length - 1];
                if (lastMessage && lastMessage.sender === 'assistant') {
                    lastMessage.content = content;
                    const messageElement = document.querySelector(`[data-message-id="${lastMessage.id}"]`);
                    if (messageElement) {
                        const contentDiv = messageElement.querySelector('.message-content');
                        if (contentDiv) {
                            contentDiv.innerHTML = content;
                        }
                    }
                } else {
                    this.addMessage('assistant', content);
                }
            }
            
            renderMessage(message) {
                const messageDiv = document.createElement('div');
                messageDiv.setAttribute('data-message-id', message.id);
                
                if (message.sender === 'user') {
                    messageDiv.className = 'flex justify-end';
                    messageDiv.innerHTML = `
                        <div class="max-w-2xl">
                            <div class="bg-red-600 text-white rounded-lg px-4 py-2">
                                <p class="text-sm">${this.escapeHtml(message.content)}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-right">
                                ${this.formatTime(message.timestamp)}
                            </p>
                        </div>
                    `;
                } else {
                    messageDiv.className = 'flex justify-start';
                    messageDiv.innerHTML = `
                        <div class="max-w-2xl">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-white rounded-lg px-4 py-2 shadow-sm border border-gray-200">
                                        ${message.isError ?
                                            `<p class="text-sm text-red-600">${this.escapeHtml(message.content)}</p>` :
                                            `<div class="text-sm text-gray-800 message-content whitespace-pre-wrap">${this.escapeHtml(message.content).replace(/\n/g, '<br>')}</div>`
                                        }
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        ${this.formatTime(message.timestamp)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                this.messagesList.appendChild(messageDiv);
            }
            
            renderLoadingMessage() {
                const loadingDiv = document.createElement('div');
                loadingDiv.id = 'loading-message';
                loadingDiv.className = 'flex justify-start';
                loadingDiv.innerHTML = `
                    <div class="max-w-2xl">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-900 animate-spin" fill="none" viewBox="0 0 24 24">
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
                `;
                
                this.messagesList.appendChild(loadingDiv);
                this.scrollToBottom();
            }
            
            removeLoadingMessage() {
                const loadingMessage = document.getElementById('loading-message');
                if (loadingMessage) {
                    loadingMessage.remove();
                }
            }
            
            setLoading(loading) {
                this.isLoading = loading;
                this.sendBtn.disabled = loading;
                this.messageInput.disabled = loading;
                
                if (loading) {
                    this.sendBtn.innerHTML = `
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    `;
                    this.renderLoadingMessage();
                } else {
                    this.sendBtn.textContent = 'Send';
                    this.removeLoadingMessage();
                }
            }
            
            showError(message) {
                const errorDiv = this.errorDisplay.querySelector('div');
                errorDiv.textContent = message;
                this.errorDisplay.classList.remove('hidden');
                
                setTimeout(() => {
                    this.errorDisplay.classList.add('hidden');
                }, 5000);
            }
            
            clearChat() {
                this.messages = [];
                this.messagesList.innerHTML = '';
                this.messagesList.classList.add('hidden');
                this.welcomeScreen.classList.remove('hidden');
                localStorage.removeItem('ai_chat_messages_' + this.sessionId);
                this.saveConversation();
            }
            
            newConversation() {
                this.clearChat();
                this.sessionId = this.generateSessionId();
                localStorage.setItem('ai_chat_current_session', this.sessionId);
            }
            
            saveConversation() {
                const sessionKey = 'ai_chat_messages_' + this.sessionId;
                localStorage.setItem(sessionKey, JSON.stringify(this.messages));
                localStorage.setItem('ai_chat_current_session', this.sessionId);
            }
            
            loadConversation() {
                const currentSession = localStorage.getItem('ai_chat_current_session');
                if (currentSession) {
                    this.sessionId = currentSession;
                    const sessionKey = 'ai_chat_messages_' + this.sessionId;
                    const savedMessages = localStorage.getItem(sessionKey);
                    
                    if (savedMessages) {
                        try {
                            this.messages = JSON.parse(savedMessages);
                            if (this.messages.length > 0) {
                                this.welcomeScreen.classList.add('hidden');
                                this.messagesList.classList.remove('hidden');
                                this.messages.forEach(msg => {
                                    msg.timestamp = new Date(msg.timestamp);
                                    this.renderMessage(msg);
                                });
                                this.scrollToBottom();
                            }
                        } catch (e) {
                            console.error('Failed to load conversation:', e);
                        }
                    }
                }
            }
            
            scrollToBottom() {
                setTimeout(() => {
                    if (this.messagesContainer) {
                        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
                    }
                }, 100);
            }
            
            formatTime(date) {
                return date.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
            }
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.aiAgentChat = new AIAgentChat();
            });
        } else {
            // DOM already loaded (in case of Livewire component update)
            window.aiAgentChat = new AIAgentChat();
        }
        
        // Re-initialize on Livewire updates
        if (typeof Livewire !== 'undefined') {
            Livewire.on('contentChanged', () => {
                setTimeout(() => {
                    if (!window.aiAgentChat) {
                        window.aiAgentChat = new AIAgentChat();
                    }
                }, 100);
            });
        }
    })();
    </script>
    
    <style nonce="{{ csp_nonce() }}">
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .animate-bounce {
            animation: bounce 1.4s ease-in-out infinite;
        }
    </style>
</div>