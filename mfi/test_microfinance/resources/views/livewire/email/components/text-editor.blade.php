<div>
    <div class="border border-gray-300 rounded-lg overflow-hidden">
        <!-- Formatting Toolbar -->
        <div class="bg-gray-50 border-b border-gray-300 p-2 flex items-center space-x-1">
            <button 
                type="button" 
                onclick="formatText('bold')"
                class="p-2 hover:bg-gray-200 rounded transition-colors"
                title="Bold"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                </svg>
            </button>
            
            <button 
                type="button" 
                onclick="formatText('italic')"
                class="p-2 hover:bg-gray-200 rounded transition-colors"
                title="Italic"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4M8 20h4m-2-16l-2 16"></path>
                </svg>
            </button>
            
            <button 
                type="button" 
                onclick="formatText('underline')"
                class="p-2 hover:bg-gray-200 rounded transition-colors"
                title="Underline"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10M7 4v8a5 5 0 0010 0V4"></path>
                </svg>
            </button>
            
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            
            <button 
                type="button" 
                onclick="formatText('insertUnorderedList')"
                class="p-2 hover:bg-gray-200 rounded transition-colors"
                title="Bullet List"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <button 
                type="button" 
                onclick="formatText('insertOrderedList')"
                class="p-2 hover:bg-gray-200 rounded transition-colors"
                title="Numbered List"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                </svg>
            </button>
            
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            
            <button 
                type="button" 
                onclick="insertLink()"
                class="p-2 hover:bg-gray-200 rounded transition-colors"
                title="Insert Link"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </button>
        </div>
        
        <!-- Editor -->
        <div 
            id="email-editor"
            contenteditable="true"
            class="min-h-[300px] p-4 focus:outline-none"
            oninput="updateEmailBody(this)"
            style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6;"
        ></div>
    </div>
    
    <!-- Hidden textarea for Livewire binding -->
    <textarea 
        wire:model="body" 
        id="email-body-hidden" 
        class="hidden"
    ></textarea>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    function formatText(command) {
        document.execCommand(command, false, null);
        document.getElementById('email-editor').focus();
    }
    
    function insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
            document.getElementById('email-editor').focus();
        }
    }
    
    function updateEmailBody(editor) {
        const hiddenTextarea = document.getElementById('email-body-hidden');
        hiddenTextarea.value = editor.innerHTML;
        hiddenTextarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Initialize editor with existing content
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('email-editor');
        const hiddenTextarea = document.getElementById('email-body-hidden');
        if (hiddenTextarea.value) {
            editor.innerHTML = hiddenTextarea.value;
        }
    });
</script>
@endpush