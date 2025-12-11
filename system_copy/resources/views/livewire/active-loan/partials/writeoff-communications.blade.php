<div>
    <!-- Member Communications Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Member Communications</h3>
            <button wire:click="sendBulkNotifications" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Send Bulk Notifications
            </button>
        </div>

        <!-- Communication Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-600 font-medium">Total Sent</p>
                <p class="text-2xl font-bold text-blue-800">
                    {{ \App\Models\WriteoffMemberCommunication::count() }}
                </p>
                <p class="text-xs text-blue-600 mt-1">All channels</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium">Delivered</p>
                <p class="text-2xl font-bold text-green-800">
                    {{ \App\Models\WriteoffMemberCommunication::where('delivery_status', 'delivered')->count() }}
                </p>
                <p class="text-xs text-green-600 mt-1">Successfully delivered</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-sm text-purple-600 font-medium">Acknowledged</p>
                <p class="text-2xl font-bold text-purple-800">
                    {{ \App\Models\WriteoffMemberCommunication::where('member_acknowledged', true)->count() }}
                </p>
                <p class="text-xs text-purple-600 mt-1">Member confirmed</p>
            </div>
            
            <div class="bg-amber-50 rounded-lg p-4">
                <p class="text-sm text-amber-600 font-medium">Failed</p>
                <p class="text-2xl font-bold text-amber-800">
                    {{ \App\Models\WriteoffMemberCommunication::where('delivery_status', 'failed')->count() }}
                </p>
                <p class="text-xs text-amber-600 mt-1">Delivery failed</p>
            </div>
        </div>

        <!-- Communications Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acknowledged</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach(\App\Models\WriteoffMemberCommunication::with(['writeOff', 'sender'])
                        ->latest('sent_date')
                        ->take(10)
                        ->get() as $communication)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $communication->sent_date->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $communication->loan_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $communication->client_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $communication->communication_type_badge['class'] }}">
                                {{ $communication->communication_type_badge['text'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $communication->delivery_status_badge['class'] }}">
                                {{ $communication->delivery_status_badge['text'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($communication->member_acknowledged)
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($communication->delivery_status === 'failed')
                                <button wire:click="resendCommunication({{ $communication->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 text-sm mr-2">
                                    Resend
                                </button>
                            @endif
                            <button wire:click="viewCommunicationDetails({{ $communication->id }})" 
                                    class="text-gray-600 hover:text-gray-900 text-sm">
                                View
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Communication Templates -->
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Communication Templates</h4>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Writeoff Initiation</h5>
                    <p class="text-xs text-gray-600">Sent when a loan is recommended for writeoff</p>
                    <div class="mt-2 flex gap-2">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">SMS</span>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Email</span>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Letter</span>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Writeoff Approval</h5>
                    <p class="text-xs text-gray-600">Sent when writeoff is officially approved</p>
                    <div class="mt-2 flex gap-2">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">SMS</span>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Email</span>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Letter</span>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Recovery Notice</h5>
                    <p class="text-xs text-gray-600">Sent during recovery efforts</p>
                    <div class="mt-2 flex gap-2">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">SMS</span>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Email</span>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Letter</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>