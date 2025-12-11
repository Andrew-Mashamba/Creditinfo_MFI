{{-- @if (in_array( "Delete Department" , session()->get('permission_items'))) --}}
<div>
    <button wire:click="deleteRole({{$id}})" class="text-white bg-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300
    font-medium rounded-lg text-sm px-2 py-1 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
        Delete
    </button>
</div>
{{-- @endif --}}
