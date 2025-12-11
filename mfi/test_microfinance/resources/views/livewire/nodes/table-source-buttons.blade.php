<div class="flex">

        <label class="inline-flex items-center">
            <input name="{{ $id }}" type="radio" wire:click="selectedOption('Database', '{{ $id }}')" value="Database" class="form-radio" {{ $dataSource == 'Database' ? 'checked' : '' }}>

            <span class="ml-2">Database</span>
        </label>

        <label class="inline-flex items-center ml-6">
            <input name ="{{ $id }}" type="radio" wire:click="selectedOption('File', '{{ $id }}')" value="File" class="form-radio" {{ $dataSource == 'File' ? 'checked' : '' }}>
            <span class="ml-2">File</span>
        </label>

</div>
