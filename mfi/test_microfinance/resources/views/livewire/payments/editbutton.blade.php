<div class="">

    @if($this->order_status == 'Processed' || $this->order_status == 'Processed With Errors' || $this->order_status == 'Processed With Exceptions' || $this->order_status == 'Processed With Exceptions And Errors' )
    @else
    <div class="flex leading-5">

        <svg xmlns="http://www.w3.org/2000/svg" wire:click="$emit('showEditModal',{{$value}})"
             width="100%" height="100%" fill="none" viewBox="0 0 24 24"
             stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
             style="cursor: pointer" class="feather feather-edit w-5 h-5 mr-1 stroke-gray-500 md:stroke-gray-700">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>

        <svg xmlns="http://www.w3.org/2000/svg" wire:click="$emit('confirmDeleteEntry',{{$value}})"
             width="100%" height="100%" fill="none" viewBox="0 0 24 24"  stroke-width="1"
             stroke-linecap="round" stroke-linejoin="round" style="cursor: pointer"
             class="feather feather-edit w-5 h-5 ml-2 mr-1 stroke-gray-500 md:stroke-gray-700">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>


</div>
    @endif
</div>
