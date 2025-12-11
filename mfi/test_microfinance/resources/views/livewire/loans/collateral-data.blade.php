<div>


    <div>


        <div class="w-full  bg-blueGray-50 rounded-3xl">
            <div class="w-full rounded-3xl">
                <div class="relative flex flex-col min-w-0 break-words w-full  bg-blueGray-100 border-0 rounded-3xl">

                    <div class="flex-auto px-4 lg:px-10 py-10 pt-0 bg-gray-100 rounded-3xl">
                        <div>
                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Collateral Identification
                            </h3>

                            <div class="flex flex-wrap">
                                <div class="w-full w-12/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Collateral ID
                                        </label>
                                        <input wire:model="CollateralID" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-gray-100 rounded-3xl text-sm shadow w-full ease-linear
                                        transition-all duration-150" value="" disabled>
                                    </div>
                                </div>

                                <div class="w-full lg:w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Client ID
                                        </label>
                                        <input wire:model="ClientID" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="" disabled>
                                    </div>
                                </div>
                                <div class="w-full lg:w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Loan ID
                                        </label>
                                        <input wire:model="LoanID" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="" disabled>
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-6 border-b-1 border-blueGray-300">

                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Basic Details
                            </h3>

                            <div class="flex flex-wrap">

                                <div class="w-full w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Collateral Category
                                        </label>
                                        <select name="collateral_category" id="collateral_category" wire:model="collateral_category" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300
                                        text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" >

                                            <option value="">Select Collateral Category</option>

                                            @foreach(DB::table('main_collateral_types')->get() as $type)
                                            {{--  --}}
                                                <option value="{{ $type->id }}">{{ $type->main_type_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="w-full w-6/12  px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Type of collateral
                                        </label>
                                        <select name="collateral_type" id="collateral_type" wire:model="collateral_type" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300
                                        text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" >

                                            <option value="">Select Type of Collateral</option>

                                            @if($this->collateral_category == 1)
                                                @foreach(DB::table('landed_property_types')->get() as $type)
                                                    <option value="{{ $type->type_name }}">{{ $type->type_name }}</option>
                                                @endforeach
                                            @endif

                                            @if($this->collateral_category == 2)
                                                @foreach(DB::table('movable_property_types')->get() as $type)
                                                    <option value="{{ $type->type_name }}">{{ $type->type_name }}</option>
                                                @endforeach
                                            @endif


                                        </select>
                                    </div>
                                </div>

                                <div class="w-full lg:w-12/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Description
                                        </label>
                                        <input wire:model="description" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="Bld Mihail Kogalniceanu, nr. 8 Bl 1, Sc 1, Ap 09">
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-6 border-b-1 border-blueGray-300">


                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Ownership Information
                            </h3>


                            <div class="flex flex-wrap">
                                <div class="w-full lg:w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Type of owner
                                        </label>
                                        <select name="type_of_owner" id="type_of_owner" wire:model="type_of_owner" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300
                                        text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" >
                                            <option value="">Select type of owner</option>
                                            <option value="Individual">Individual</option>
                                            <option value="Company">Company</option>

                                        </select>
                                    </div>
                                </div>

                                <div class="w-full lg:w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Relationship of the owner to the borrower
                                        </label>
                                        <select name="relationship" id="relationship" wire:model="relationship" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300
                                        text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" >

                                            <option value="Borrower">Borrower</option>
                                            <option value="Guarantor">Guarantor</option>

                                        </select>
                                    </div>
                                </div>

                                @if($this->type_of_owner == 'Individual')
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Full Name
                                            </label>
                                            <input wire:model="collateral_owner_full_name" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner NIDA
                                            </label>
                                            <input wire:model="collateral_owner_nida" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Contact Number
                                            </label>
                                            <input wire:model="collateral_owner_contact_number" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Residential Address
                                            </label>
                                            <input wire:model="collateral_owner_residential_address" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Spouse Full Name
                                            </label>
                                            <input wire:model="collateral_owner_spouse_full_name" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Spouse NIDA
                                            </label>
                                            <input wire:model="collateral_owner_spouse_nida" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Spouse Contact Number
                                            </label>
                                            <input wire:model="collateral_owner_spouse_contact_number" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Collateral Owner Spouse Residential Address
                                            </label>
                                            <input wire:model="collateral_owner_spouse_residential_address" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                @endif

                                @if($this->type_of_owner == 'Company')
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Company Registered Name
                                            </label>
                                            <input wire:model="company_registered_name" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Business Licence Number
                                            </label>
                                            <input wire:model="business_licence_number" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Tax Identification Number (TIN) Certificate
                                            </label>
                                            <input wire:model="TIN" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Director's NIDA
                                            </label>
                                            <input wire:model="director_nida" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Director's Contact Number
                                            </label>
                                            <input wire:model="director_contact" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Director's Residential Address
                                            </label>
                                            <input wire:model="director_address" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                    <div class="w-full lg:w-6/12 px-4">
                                        <div class="relative w-full mb-3">
                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Business Address
                                            </label>
                                            <input wire:model="business_address" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                        </div>
                                    </div>
                                @endif





                            </div>

                            <hr class="mt-6 border-b-1 border-blueGray-300">


                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Documentation
                            </h3>
                            <div class="flex flex-wrap px-4">

                                @foreach(\Illuminate\Support\Facades\DB::table('document_types')->where('collateral_type',$this->collateral_type)->get() as $document)



                                {{-- @foreach(\Illuminate\Support\Facades\DB::table('users')->get() as $document) --}}
                                    <div class="w-full mb-2 border-1 border-green-100 bg-white rounded-3xl shadow flex px-4">
                                        <label class="text-sm font-medium w-1/4 my-auto" for="document_{{ $document->document_id }}">
                                            {{ $document->document_name }}
                                        </label>

                                        <div class="w-3/4 flex justify-between text-center flex">
                                            <div class="w-2/3">
                                                <input wire:model="fileUploads.{{ $document->document_id }}" type="file" id="document_{{ $document->document_id }}" name="documents[]" multiple class="border-0 px-3 py-3 text-sm focus:outline-none focus:ring ease-linear transition-all duration-150">

                                            </div>
                                            @if(
                                            $document->document_name =='Collateral Image I' or
                                                 $document->document_name =='Collateral Image II' or
                                                 $document->document_name =='Collateral Image III'

                                            )


                                            @else
                                                <div class="w-1/3">
                                                    <div class="text-xs align-text-left">Expiring Date</div>
                                                    <input wire:model="ExpireDates.{{ $document->document_id }}" type="date" id="date_{{ $document->document_id }}" name="dates[]" multiple class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-gray-100 rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear transition-all duration-150" value="Expiring Date">

                                                </div>
                                            @endif



                                        </div>
                                    </div>
                                @endforeach

                            </div>

                            <hr class="mt-6 border-b-1 border-blueGray-300">


                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Valuation Information
                            </h3>
                            <div class="flex flex-wrap">
                                <div class="w-full lg:w-12/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Force value of the collateral
                                        </label>
                                        <input wire:model="collateral_value" type="number" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Date of valuation
                                        </label>
                                        <input wire:model="date_of_valuation" type="date" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Valuation method used
                                        </label>
                                        <input wire:model="valuation_method_used"  type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="United States">
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Name of valuer
                                        </label>
                                        <input wire:model="name_of_valuer"  type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="Postal Code">
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-6 border-b-1 border-blueGray-300">

                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Insurance Information
                            </h3>
                            <div class="flex flex-wrap">
                                <div class="w-full lg:w-12/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Insurance Policy Number
                                        </label>
                                        <input wire:model="policy_number" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Insurance Company Name
                                        </label>
                                        <input wire:model="company_name" type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="">
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Insurance Coverage Details
                                        </label>
                                        <input wire:model="coverage_details"  type="text" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="United States">
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Expiration Date of Insurance Coverage
                                        </label>
                                        <input wire:model="expiration_date"  type="date" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150" value="Postal Code">
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-6 border-b-1 border-blueGray-300">


                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Collateral Condition and Status
                            </h3>
                            <div class="flex flex-wrap">
                                <div class="w-full w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Physical Condition
                                        </label>

                                        <select wire:model="physical_condition" id="physical_condition" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150">
                                            <option value="excellent">Excellent</option>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="w-full w-6/12 px-4">
                                    <div class="relative w-full mb-3">
                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Current Status of Collateral
                                        </label>
                                        <select wire:model="current_status" id="physical_condition" class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600
                                        bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear
                                        transition-all duration-150">
                                            <option value="active">Active</option>
                                            <option value="under_dispute">Under Dispute</option>
                                            <option value="discharged">Discharged</option>
                                        </select>
                                    </div>
                                </div>




                            </div>



                            <hr class="mt-6 border-b-1 border-blueGray-300">

                            <h3 class="uppercase font-medium text-sm mt-3 mb-6 tracking-tight text-gray-900 leading-tight">
                                Location of the collateral
                            </h3>
                            <div class="flex flex-wrap">
                                <div class="w-full lg:w-12/12 px-4">
                                    <div class="relative w-full mb-3">

                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Physical Address
                                        </label>
                                        <input
                                            wire:model="address"
                                            type="text"
                                            class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                                        />
                                    </div>
                                </div>
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">

                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            House / Plot Number
                                        </label>
                                        <input
                                            wire:model="building_number"
                                            type="text"
                                            class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded-3xl text-sm shadow
                                                focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                                        />
                                    </div>
                                </div>

                                {{-- region--}} {{-- country--}}
                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">

                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Postal Code
                                        </label>
                                        <input
                                            wire:model="postal_code"
                                            type="text"
                                            class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded-3xl text-sm shadow focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                                        />
                                    </div>
                                </div>


                                <div class="w-full lg:w-4/12 px-4">
                                    <div class="relative w-full mb-3">

                                        <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                            Region
                                        </label>
                                        <select
                                            wire:model="region"
                                            id="regionsSelect"
                                            class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded-3xl text-sm shadow
                                                focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                                        >
                                            <option value=""> Chagua Mkoa </option>
                                            @foreach( DB::table('regions')->get() as $region)
                                                <option value="{{$region->region}}"> {{$region->region}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="w-full flex mb-3">

                                    @if($this->region)


                                        <div class="w-1/2 px-4">

                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                District
                                            </label>
                                            <select
                                                id="districtsSelect"
                                                wire:model="district"
                                                class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded-3xl text-sm shadow
                                                focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                                            >
                                                <option value="">Chagua Wilaya</option>
                                                @foreach( DB::table('districts')->where('region',$this->region.' Region')->get() as $district)
                                                    <option value="{{$district->district}}"> {{$district->district}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    @endif

                                    @if($this->district)

                                        <div class="w-1/2 px-4">

                                            <label class="text-sm font-medium mb-2" htmlfor="grid-password">
                                                Ward
                                            </label>
                                            <select
                                                id="wardsSelect"
                                                wire:model="ward"
                                                class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded-3xl text-sm shadow
                                                focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                                            >
                                                <option value="">Chagua Kata</option>
                                                @foreach( DB::table('wards')->where('district',$this->district)->get() as $ward)
                                                    <option value="{{$ward->ward}}"> {{$ward->ward}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    @endif

                                </div>






                            </div>



                        </div>
                    </div>
                </div>
                <footer class="relative  pt-8 pb-6 mt-2">
                    <div class="container mx-auto px-4">
                        <div class="flex flex-wrap items-center md:justify-between justify-center">
                            <div class="w-full md:w-6/12 px-4 mx-auto text-center">
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>



</div>
