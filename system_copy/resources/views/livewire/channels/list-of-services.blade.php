<div>
    @foreach ( $servicesListing as $serviceid)
        <div class="bg-gray-200 rounded-lg p-2 m-2">
            <p class="text-gray-500">{{ App\Models\servicesModel::where('ID', $serviceid)->value('NAME') }}</p>
        </div>
    @endforeach
</div>
