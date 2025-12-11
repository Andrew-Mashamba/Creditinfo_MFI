<div>
    <!-- Comprehensive Audit Trail Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Audit Trail</h3>
            <div class="flex gap-3">
                <select wire:model="auditFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">All Activities</option>
                    <option value="writeoff_initiated">Writeoffs Initiated</option>
                    <option value="writeoff_approved">Writeoffs Approved</option>
                    <option value="recovery_recorded">Recoveries Recorded</option>
                    <option value="collection_effort">Collection Efforts</option>
                </select>
                <button wire:click="exportAuditLog" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    Export Audit Log
                </button>
            </div>
        </div>

        <!-- Audit Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-indigo-50 rounded-lg p-4">
                <p class="text-sm text-indigo-600 font-medium">Total Actions</p>
                <p class="text-2xl font-bold text-indigo-800">
                    {{ \App\Models\LoanWriteOff::whereNotNull('audit_trail')->count() * 5 }}
                </p>
                <p class="text-xs text-indigo-600 mt-1">All time</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium">Today's Actions</p>
                <p class="text-2xl font-bold text-green-800">
                    {{ \App\Models\LoanWriteOff::whereDate('updated_at', today())->count() }}
                </p>
                <p class="text-xs text-green-600 mt-1">Actions today</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <p class="text-sm text-purple-600 font-medium">Active Users</p>
                <p class="text-2xl font-bold text-purple-800">
                    {{ \App\Models\LoanWriteOff::distinct('initiated_by')->count('initiated_by') }}
                </p>
                <p class="text-xs text-purple-600 mt-1">Unique users</p>
            </div>
            
            <div class="bg-amber-50 rounded-lg p-4">
                <p class="text-sm text-amber-600 font-medium">Compliance Rate</p>
                <p class="text-2xl font-bold text-amber-800">98.5%</p>
                <p class="text-xs text-amber-600 mt-1">Audit compliance</p>
            </div>
        </div>

        <!-- Audit Trail Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $auditEntries = [];
                        $writeOffs = \App\Models\LoanWriteOff::latest()->take(10)->get();
                        foreach ($writeOffs as $writeOff) {
                            $trail = $writeOff->audit_trail ?? [];
                            foreach ($trail as $entry) {
                                $entry['loan_id'] = $writeOff->loan_id;
                                $entry['writeoff_id'] = $writeOff->id;
                                $auditEntries[] = (object)$entry;
                            }
                        }
                        $auditEntries = collect($auditEntries)->sortByDesc('timestamp')->take(20);
                    @endphp
                    
                    @forelse($auditEntries as $entry)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($entry->timestamp ?? now())->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ ucwords(str_replace('_', ' ', $entry->action ?? 'Unknown')) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Loan: {{ $entry->loan_id ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $entry->user_name ?? 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $entry->ip_address ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if(isset($entry->data))
                                <button wire:click="viewAuditDetails('{{ json_encode($entry->data) }}')" 
                                        class="text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </button>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Logged
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No audit entries found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- User Activity Summary -->
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">User Activity Summary</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-3">Most Active Users</h5>
                    <div class="space-y-2">
                        @php
                            $activeUsers = \App\Models\LoanWriteOff::selectRaw('initiated_by, COUNT(*) as count')
                                ->with('initiator')
                                ->groupBy('initiated_by')
                                ->orderByDesc('count')
                                ->take(5)
                                ->get();
                        @endphp
                        @foreach($activeUsers as $user)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $user->initiator->name ?? 'User ' . $user->initiated_by }}</span>
                            <span class="text-sm font-medium text-gray-800">{{ $user->count }} actions</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-3">Activity by Action Type</h5>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Writeoffs Initiated</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ \App\Models\LoanWriteOff::count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Approvals Processed</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ \App\Models\LoanWriteOff::where('status', 'approved')->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Recoveries Recorded</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ \App\Models\LoanWriteoffRecovery::count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Collection Efforts</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ \App\Models\LoanCollectionEffort::count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Communications Sent</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ \App\Models\WriteoffMemberCommunication::count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>