<div class="m-12">

        <!-- Add Team Client -->
        <div class="mt-10 sm:mt-0">


            <div >

                <div class="col-span-6">
                    <div class="max-w-xl text-sm text-gray-600">
                        {{ __('Please select a user, among pending un assigned users.') }}
                    </div>
                </div>



                <div class="form-group col-span-6 sm:col-span-4">

                    <x-jet-label for="pendinguser" value="{{ __('Select a user') }}" />
                    <select id="pendinguser" wire:model="pendinguser" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                        <option value="" selected>Select</option>

                        @foreach($this->pendingUsers as $pendingUser)
                            <option value="{{$pendingUser->id}}">{{$pendingUser->name}} : {{$pendingUser->email}}</option>

                        @endforeach
                    </select>

                </div>



                <div class="form-group col-span-6 sm:col-span-4">

                    <x-jet-label for="department" value="{{ __('Select department') }}" />
                    <select id="department" wire:model="department" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                        <option value="" selected>Select</option>

                        @foreach($this->departmentList as $department)
                            <option value="{{$department->id}}">{{$department->department_name}}</option>

                        @endforeach
                    </select>

                </div>


                <div class="form-group col-span-6 sm:col-span-4">

                    <x-jet-label for="userrole" value="{{ __('Select user role') }}" />
                    <select id="userrole" wire:model="userrole" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                        <option value="" selected>Select</option>
                        <option value="Teller" >Teller</option>
                        <option value="Officer" >Officer / In putter</option>
                        <option value="Authorizer" >Authorizer</option>

                    </select>

                </div>


                <button wire:click='save' class="mt-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray uppercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                    SAVE
                </button>

            </div>


        </div>

        <div>
            <h2 class="text-lg font-semibold mb-4">User Assignments</h2>
            <form wire:submit.prevent="saveAssignments">
                <div class="mb-4">
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department" wire:model="department_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_code }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="committees" class="block text-sm font-medium text-gray-700">Committees</label>
                    <select id="committees" wire:model="selectedCommittees" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($committees as $committee)
                            <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="branch" class="block text-sm font-medium text-gray-700">Branch</label>
                    <select id="branch" wire:model="branch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->branch_code }}">{{ $branch->branch_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="institution" class="block text-sm font-medium text-gray-700">Institution</label>
                    <select id="institution" wire:model="institution_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Institution</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-900">Save Assignments</button>
            </form>
        </div>



</div>
