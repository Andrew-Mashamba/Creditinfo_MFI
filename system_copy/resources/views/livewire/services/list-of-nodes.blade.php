<div>
    @foreach ( $nodeslisting as $nodeid)
        <div class="bg-gray-200 rounded-lg p-2 m-2">
            <p class="text-gray-500">{{ App\Models\NodesList::where('ID', $nodeid)->where('NODE_STATUS','ACTIVE')->value('NODE_NAME') }}</p>
        </div>
    @endforeach
</div>
