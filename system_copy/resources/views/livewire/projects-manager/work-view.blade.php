<div>
	<div class="p-4">
		<!-- Welcome banner -->

		<!-- Dashboard actions -->
		<div class="flex w-full mb-4 gap-2 justify-between">
			<!-- Left: Avatars -->
			<div class="bg-white w-full p-1 flex  items-center " style="height: 70px">
				<div class="inline-flex p-2">
					<button wire:click="setView(2)" class="mr-4 flex text-center items-center @if($this->selected == 2) lighter-shade @else bg-gray-100  @endif hover:lighter-shade @if($this->selected == 2)  text-white  @else  text-gray-400    @endif font-semibold py-2 px-4 rounded-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /> </svg> Task List </button>
					<button wire:click="setView(1)" class="mr-4 flex text-center items-center @if($this->selected == 1) lighter-shade @else bg-gray-100  @endif hover:lighter-shade @if($this->selected == 1)  text-white  @else  text-gray-400    @endif font-semibold py-2 px-4 rounded-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /> </svg> Create Task </button>
                    <button wire:click="setView(3)" class="mr-4 flex text-center items-center @if($this->selected == 3) lighter-shade @else bg-gray-100  @endif hover:lighter-shade @if($this->selected == 3)  text-white  @else  text-gray-400    @endif font-semibold py-2 px-4 rounded-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /> </svg> Create Milestone </button>
                    <button wire:click="setView(4)" class="mr-4 flex text-center items-center @if($this->selected == 4) lighter-shade @else bg-gray-100  @endif hover:lighter-shade @if($this->selected == 4)  text-white  @else  text-gray-400    @endif font-semibold py-2 px-4 rounded-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 fill-current">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /> </svg> Create Issue </button>
				</div>
			</div>

			<div>
				<div wire:loading.remove wire:target="back" >
				<div class="flex items-center space-x-5" >
					<svg xmlns="http://www.w3.org/2000/svg" wire:click="back" class="cursor-pointer h-12 bg-slate-50 rounded-full stroke-blue-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
					</svg>


				</div>
				</div>

				<div wire:loading wire:target="back" >

					<div class="flex items-center">
						<svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-12 w-12  stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

						</svg>

					</div>

				</div>
			</div>
			<!-- Right: Actions -->
		</div>
		<div class="bg-white p-4 ">
			<div class="tab-pane fade " id="tabs-homeJustify" role="tabpanel" aria-labelledby="tabs-home-tabJustify">
				<div class="mt-2"></div>
				<div class="w-full flex items-center justify-center">
					<div wire:loading wire:target="setView">
						<div class="h-96 m-auto flex items-center justify-center">
							<div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
						</div>
					</div>
				</div>
				<div wire:loading.remove wire:target="setView" class="tssssst"> @switch($this->selected) @case('1')
					<livewire:projects-manager.create-a-task/> @break @case('2')
					<livewire:projects-manager.task-list/> @break @case('3')
					<livewire:projects-manager.create-a-milestone/> @break @case('4')
					<livewire:projects-manager.create-an-issue/> @break @default
					<livewire:projects-manager.project-list/> @endswitch </div>
			</div>
		</div>
	</div>
</div>