{{-- Error Notification Component --}}
<div id="error-notification" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-4 max-w-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3 w-0 flex-1">
                <h3 class="text-sm font-medium text-gray-900" id="error-title">Error</h3>
                <div class="mt-2 text-sm text-gray-500" id="error-message"></div>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button type="button" onclick="hideErrorNotification()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Validation Errors Component --}}
<div id="validation-errors" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-4 max-w-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3 w-0 flex-1">
                <h3 class="text-sm font-medium text-gray-900">Validation Errors</h3>
                <div class="mt-2 text-sm text-gray-500" id="validation-messages"></div>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button type="button" onclick="hideValidationErrors()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    // Show error notification
    function showErrorNotification(message) {
        const notification = document.getElementById('error-notification');
        const errorMessage = document.getElementById('error-message');
        errorMessage.textContent = message;
        notification.classList.remove('hidden');
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            hideErrorNotification();
        }, 5000);
    }

    // Hide error notification
    function hideErrorNotification() {
        const notification = document.getElementById('error-notification');
        notification.classList.add('hidden');
    }

    // Show validation errors
    function showValidationErrors(errors) {
        const container = document.getElementById('validation-errors');
        const messagesContainer = document.getElementById('validation-messages');
        
        // Clear previous messages
        messagesContainer.innerHTML = '';
        
        // Add each error message
        Object.entries(errors).forEach(([field, messages]) => {
            const fieldName = field.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            messages.forEach(message => {
                const messageElement = document.createElement('p');
                messageElement.className = 'text-red-600 mb-1';
                messageElement.textContent = `${fieldName}: ${message}`;
                messagesContainer.appendChild(messageElement);
            });
        });
        
        container.classList.remove('hidden');
        
        // Auto hide after 8 seconds
        setTimeout(() => {
            hideValidationErrors();
        }, 8000);
    }

    // Hide validation errors
    function hideValidationErrors() {
        const container = document.getElementById('validation-errors');
        container.classList.add('hidden');
    }

    // Listen for Livewire events
    document.addEventListener('livewire:load', function () {
        Livewire.on('show-error', data => {
            showErrorNotification(data.message);
        });

        Livewire.on('show-validation-errors', data => {
            showValidationErrors(data.errors);
        });
    });
</script> 