<div class="">
    <div class="flex flex-wrap w-full border-blue-500 rounded-md">
        @php
            $str_json = json_encode($permissions);
            $menuItems = json_decode($str_json, TRUE);
            
            if (is_array($menuItems)) {
                $menuItems = str_replace(array('[',']','"',' '), '', $menuItems);
                $menuItems = explode(",", $menuItems);
                // Filter out empty values and sort
                $menuItems = array_filter($menuItems);
                sort($menuItems);
            } else {
                $menuItems = [];
            }
        @endphp

        @if(count($menuItems) > 0)
            @foreach($menuItems as $permission)
                @if(!empty($permission))
                    <div class="flex-shrink-0 w-1/4 p-2">
                        @php
                            $action = App\Models\sub_menus::where('id', $permission)->value('user_action');
                        @endphp
                        @if($action)
                            <span class="text-sm">{{ $action }}</span>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="w-full p-2 text-sm text-gray-500">
                No permissions assigned
            </div>
        @endif
    </div>
</div>
