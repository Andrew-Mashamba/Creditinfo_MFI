@props(['field', 'label' => 'Upload File', 'multiple' => false, 'accept' => null])

<div class="file-upload">
    <input type="file" 
           id="{{ $field }}"
           wire:model="{{ $field }}"
           @if($multiple) multiple @endif
           @if($accept) accept="{{ $accept }}" @endif
           class="hidden">
    
    <button type="button" 
            onclick="document.getElementById('{{ $field }}').click()"
            class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        {{ $label }}
    </button>

    @if($files)
        <div class="mt-2">
            @if($multiple)
                @foreach($files as $index => $file)
                    <div class="flex items-center justify-between p-2 mt-1 text-sm bg-gray-50 rounded">
                        <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                        <button type="button" 
                                wire:click="removeFile('{{ $field }}', {{ $index }})"
                                class="ml-2 text-red-600 hover:text-red-800">
                            Remove
                        </button>
                    </div>
                @endforeach
            @else
                <div class="flex items-center justify-between p-2 mt-1 text-sm bg-gray-50 rounded">
                    <span class="truncate">{{ $files->getClientOriginalName() }}</span>
                    <button type="button" 
                            wire:click="removeFile('{{ $field }}')"
                            class="ml-2 text-red-600 hover:text-red-800">
                        Remove
                    </button>
                </div>
            @endif
        </div>
    @endif

    @error($field)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<style nonce="{{ csp_nonce() }}">
.file-upload-wrapper {
    @apply relative;
}

.file-upload-input {
    @apply hidden;
}

.file-upload-button {
    @apply flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500;
}

.file-upload-text {
    @apply text-sm text-gray-700;
}

.file-preview-section {
    @apply mt-2 space-y-2;
}

.file-preview-item {
    @apply flex items-center justify-between p-2 bg-gray-50 rounded-md;
}

.file-name {
    @apply text-sm text-gray-600 truncate;
}

.remove-file-button {
    @apply text-gray-400 hover:text-gray-500 focus:outline-none;
}

.error-message {
    @apply mt-1 text-sm text-red-600;
}
</style> 