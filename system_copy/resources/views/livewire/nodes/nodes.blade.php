<div>
    <div class="p-4">

        <!-- Welcome banner -->
        <div class="relative bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200" style="height: 116px;">

            <!-- Content -->
            <div class="">
                <div class="w-full flex items-center mb-2 space-x-2 text-sm font-semibold spacing-sm text-slate-600 h-auto">

                    <div class="w-full">
                        <div class="text-blue-500">NODES STATUS</div>

                        <table >

                            <tbody>
                            <tr>
                                <td ><p class="mr-2">Available Nodes</p></td>
                                <td ><p class="">:</p></td>
                                <td ><p class="mr-2 ml-2">{{$this->totalNodes}} nodes</p></td>
                            </tr>
                            <tr>
                                <td><p class="mr-2">Inactive Nodes</p></td>
                                <td>:</td>
                                <td><p class="mr-2 ml-2">{{$this->inActiveNodes}} nodes</p></td>
                            </tr>

                            </tbody>
                        </table>


                    </div>

                </div>



            </div>




        </div>



        <!-- Dashboard actions -->
        <div class="flex w-full mb-4 gap-2">

            <!-- Left: Avatars -->
            <div class="bg-white rounded-2xl shadow-md shadow-gray-200 w-full flex  items-center  p-1" style="height: 70px">

                <div class="inline-flex p-2" >
                    @if (in_array(7, session()->get('permissions')))

                    <button wire:click="setView(1)" class="mr-4 flex text-center items-center @if($this->selected == 1) very-light-shade @else bg-gray-100  @endif  @if($this->selected == 1) text-blue-400 font-bold  @else  text-gray-400 font-semibold   @endif  py-2 px-4 rounded-lg" onmouseover="this.style.backgroundColor='#B4E4FC'; this.style.color='#60A5FA';" onmouseout="this.style.backgroundColor=''; this.style.color='';">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>

                        Node List
                    </button>
                    @endif
                    @if (in_array(8, session()->get('permissions')))

                    <button wire:click="setView(2)" class="mr-4 flex text-center items-center @if($this->selected == 2) very-light-shade @else bg-gray-100  @endif  @if($this->selected == 2) text-blue-400 font-bold  @else  text-gray-400 font-semibold   @endif  py-2 px-4 rounded-lg" onmouseover="this.style.backgroundColor='#B4E4FC'; this.style.color='#60A5FA';" onmouseout="this.style.backgroundColor=''; this.style.color='';">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>

                        Add A Node
                    </button>
                     @endif
                     @if (in_array(9, session()->get('permissions')))

                    <button wire:click="setView(3)" class="mr-4 flex text-center items-center @if($this->selected == 3) very-light-shade @else bg-gray-100  @endif @if($this->selected == 3) text-blue-400 font-bold  @else  text-gray-400 font-semibold   @endif  py-2 px-4 rounded-lg" onmouseover="this.style.backgroundColor='#B4E4FC'; this.style.color='#60A5FA';" onmouseout="this.style.backgroundColor=''; this.style.color='';">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                        </svg>


                        Edit A Node
                    </button>
                    @endif
                    @if (in_array(10, session()->get('permissions')))

                    <button wire:click="setView(4)" class="mr-4 flex text-center items-center @if($this->selected == 4) very-light-shade @else bg-gray-100  @endif @if($this->selected == 4) text-blue-400 font-bold  @else  text-gray-400 font-semibold   @endif  py-2 px-4 rounded-lg" onmouseover="this.style.backgroundColor='#B4E4FC'; this.style.color='#60A5FA';" onmouseout="this.style.backgroundColor=''; this.style.color='';">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>


                        Delete A Node
                    </button>
                    @endif
                </div>


            </div>



        </div>


        <div class="bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200">

                <div class="tab-pane fade " id="tabs-homeJustify"
                     role="tabpanel" aria-labelledby="tabs-home-tabJustify">
                    <div class="mt-2"></div>
                    <div class="w-full flex items-center justify-center">
                        <div wire:loading wire:target="setView">
                            <div class="h-96 m-auto flex items-center justify-center">
                                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
                            </div>
                        </div>
                    </div>
                    <div wire:loading.remove wire:target="setView">
                        @switch($this->selected)
                            @case('1')
                                <livewire:nodes.nodes-table/>
                                @break
                            @case('2')
                                <livewire:nodes.add-node/>
                                @break
                            @case('3')
                                <livewire:nodes.edit-node/>
                                @break
                            @case('4')
                                <livewire:nodes.delete-node/>
                                @break

                            @default
                                <livewire:nodes.nodes-table/>
                        @endswitch
                    </div>

                </div>



        </div>


    </div>

</div>

