@if($showEditBranch)
<div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 w-3/4 bg-white rounded-lg shadow-2xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Edit Branch</h3>
                <p class="text-sm text-gray-500 mt-1">Branch #{{ $branch_number }}</p>
            </div>
            <button wire:click="closeModal"
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 p-1 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="updateBranch">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h4>

                        <div class="space-y-4">
                            <div>
                                <label for="branch_number" class="block text-sm font-medium text-gray-700">Branch Number</label>
                                <input id="branch_number" type="text" disabled
                                    class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="branch_number">
                                @error('branch_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Branch Name</label>
                                <input id="name" type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="name">
                                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700">Region</label>
                                <input id="region" type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="region">
                                @error('region') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="wilaya" class="block text-sm font-medium text-gray-700">Wilaya</label>
                                <input id="wilaya" type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="wilaya">
                                @error('wilaya') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="branch_type" class="block text-sm font-medium text-gray-700">Branch Type</label>
                                <select id="branch_type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="branch_type">
                                    <option value="">Select Type</option>
                                    <option value="MAIN">Main Branch</option>
                                    <option value="SUB">Sub Branch</option>
                                    <option value="MOBILE">Mobile Branch</option>
                                </select>
                                @error('branch_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Contact Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input id="email" type="email"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="email">
                                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input id="phone_number" type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="phone_number">
                                @error('phone_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input id="address" type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="address">
                                @error('address') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Branch Management -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Branch Management</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="branch_manager" class="block text-sm font-medium text-gray-700">Branch Manager</label>
                                <input id="branch_manager" type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="branch_manager">
                                @error('branch_manager') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="opening_date" class="block text-sm font-medium text-gray-700">Opening Date</label>
                                <input id="opening_date" type="date"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    wire:model.defer="opening_date">
                                @error('opening_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="opening_time" class="block text-sm font-medium text-gray-700">Opening Time</label>
                                    <input id="opening_time" type="time"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        wire:model.defer="opening_time">
                                    @error('opening_time') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="closing_time" class="block text-sm font-medium text-gray-700">Closing Time</label>
                                    <input id="closing_time" type="time"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        wire:model.defer="closing_time">
                                    @error('closing_time') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Services Offered</h4>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach(['SAVINGS', 'LOANS', 'INSURANCE', 'INVESTMENT'] as $service)
                                <label class="inline-flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" value="{{ $service }}" wire:model.defer="services_offered"
                                        class="form-checkbox h-5 w-5 text-indigo-600">
                                    <span class="ml-3 text-sm font-medium text-gray-700">{{ ucfirst(strtolower($service)) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('services_offered') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 flex justify-end space-x-3 border-t pt-4 border-gray-200">
                <button type="button" wire:click="closeModal"
                    class="py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="py-2 px-4 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endif