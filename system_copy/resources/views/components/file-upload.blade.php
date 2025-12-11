@props(['field', 'label' => 'Upload File', 'multiple' => false, 'accept' => null])

<div class="file-upload-wrapper">
    <input type="file" wire:model="{{ $field }}" class="file-upload-input" {{ $multiple ? 'multiple' : '' }} {{ $accept ? 'accept='.$accept : '' }}>
    <div class="file-upload-button">
        <svg class="file-upload-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="file-upload-text">{{ $label }}</span>
    </div>
</div>

<div class="file-preview">
    @if($multiple)
        @if(isset(${$field}) && is_array(${$field}))
            @foreach(${$field} as $index => $file)
                <div class="file-preview-item">
                    <span class="file-preview-name">{{ $file->getClientOriginalName() }}</span>
                    <button type="button" wire:click="removeFile('{{ $field }}', {{ $index }})" class="file-preview-remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        @endif
    @else
        @if(isset(${$field}))
            <div class="file-preview-item">
                <span class="file-preview-name">{{ ${$field}->getClientOriginalName() }}</span>
                <button type="button" wire:click="removeFile('{{ $field }}')" class="file-preview-remove">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    @endif
</div>

@error($field) <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror 