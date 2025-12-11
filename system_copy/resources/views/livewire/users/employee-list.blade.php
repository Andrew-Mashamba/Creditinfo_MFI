<div class="" >


    <div class="flex flex-wrap  w-full  border-blue-500 rounded-md">



        @foreach(DB::table('committee_membership')->where('committee_id',$id)->get() as $value )
            <div class="flex-shrink-0 w-1/4 p-2">
                @php
                $employee= DB::table('employees')->where('id',$value->id)->first();
                if($employee){
                 $name = $employee->first_name.' '.$employee->middle_name.' '.$employee->last_name;
                }
                     @endphp

                     <span class="text-sm"> {{ $name ?? "N/A" }}   </span>
            </div>
        @endforeach

    </div>

</div>
