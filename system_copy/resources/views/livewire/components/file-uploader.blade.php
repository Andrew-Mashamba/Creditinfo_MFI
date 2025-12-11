<div class="file-uploader {{ $customClass }}">
    <div class="relative">
        <input 
            type="file" 
            wire:model="uploadedFiles" 
            class="file-upload-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @if($multiple) multiple @endif
            @if($accept) accept="{{ $accept }}" @endif
        >
        
        @if($showProgress)
            <div wire:loading wire:target="uploadedFiles" class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-900 h-2.5 rounded-full animate-pulse" style="width: 45%"></div>
                </div>
                <p class="text-sm text-gray-500 mt-1">Uploading...</p>
            </div>
        @endif
    </div>

    @error('uploadedFiles.*') 
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    @if($preview && count($uploadedFiles) > 0)
        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($uploadedFiles as $index => $file)
                <div class="relative group">
                    @if(str_contains($file->getMimeType(), 'image/'))
                        <img src="{{ $file->temporaryUrl() }}" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                    @else
                        <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                        <button 
                            wire:click="removeFile({{ $index }})"
                            class="text-white hover:text-red-500 transition-colors"
                            title="Remove file"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 truncate">{{ $file->getClientOriginalName() }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div> 