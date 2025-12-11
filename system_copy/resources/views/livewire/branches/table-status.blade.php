<div class="mt-2">
    @if($status == 'ACTIVE')
        <span class="bg-green-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'ONPROGRESS')
        <span class="bg-blue-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'PENDING')
        <span class="bg-yellow-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'NEW CLIENT')
        <span class="bg-orange-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'DELETED')
        <span class="bg-red-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'NEW LOAN')
        <span class="bg-gray-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'APPROVED')
        <span class="bg-green-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'REJECTED')
        <span class="bg-red-600 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'CLOSED')
        <div class="space-x-4 ">
            <span class="bg-red-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
        </div>
    @endif
        @if($status == 'BLOCKED')
            <div class="space-x-4 ">
                <span class="bg-red-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
            </div>
        @endif
    @if($status == 'ACCEPTED')
        <span class="bg-green-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'AWAITING DISBURSEMENT')
        <span class="bg-purple-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
    @if($status == 'AWAITING APPROVAL' )
        <span class="bg-orange-400 text-white text-xs font-medium mr-2 p-2 rounded">{{$status}}</span>
    @endif
</div>
