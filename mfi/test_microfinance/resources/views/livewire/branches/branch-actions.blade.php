@props(['id', 'canView', 'canEdit', 'canDelete'])

{{-- Debug output --}}
@php
    Log::info('Branch actions view rendering', [
        'id' => $id,
        'canView' => $canView,
        'canEdit' => $canEdit,
        'canDelete' => $canDelete
    ]);
@endphp

<div class="flex space-x-2 items-center">
   
    @if($canView)
    <button type="button" wire:click="viewBranch({{ $id }})" 
        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
        <i class="fas fa-eye mr-1.5"></i> View
    </button>
    @endif

    @if($canEdit)
    <button type="button" wire:click="editBranch({{ $id }})" 
        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
        <i class="fas fa-edit mr-1.5"></i> Edit
    </button>
    @endif

    @if($canDelete)
    <button type="button" wire:click="blockBranch({{ $id }})" 
        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
        <i class="fas fa-trash mr-1.5"></i> Delete
    </button>
    @endif
</div>

{{-- Debug output for visibility --}}
@if(!$canView && !$canEdit && !$canDelete)
<div class="text-red-500 text-sm">
    No permissions available
</div>
@endif 