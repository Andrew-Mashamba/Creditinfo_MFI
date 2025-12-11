{{-- @if (in_array( "Delete Department" , session()->get('permission_items'))) --}}
<div>
    <button wire:click="deleteRole({{$id}})" class="text-white bg-red-600 hover:bg-red-900 focus:ring-4 focus:outline-none focus:ring-blue-300
    font-medium rounded-lg text-sm px-2 py-1 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
        Delete
    </button>
</div>
{{-- @endif --}}
