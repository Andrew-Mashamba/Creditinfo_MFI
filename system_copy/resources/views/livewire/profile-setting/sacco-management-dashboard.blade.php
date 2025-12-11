<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">SACCO Management Dashboard</h1>
            <p class="text-gray-600">Manage committees, members, meetings, attendance, and documents in one place.</p>
        </div>
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('tab', 'committees')" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'committees' ? 'border-blue-900 text-blue-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Committees</button>
                <button wire:click="$set('tab', 'committee_members')" @if(!$selectedCommitteeId) disabled @endif class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'committee_members' ? 'border-blue-900 text-blue-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} {{ !$selectedCommitteeId ? 'opacity-50 cursor-not-allowed' : '' }}">Committee Members</button>
                <button wire:click="$set('tab', 'meetings')" @if(!$selectedCommitteeId) disabled @endif class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'meetings' ? 'border-blue-900 text-blue-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} {{ !$selectedCommitteeId ? 'opacity-50 cursor-not-allowed' : '' }}">Meetings</button>
                <button wire:click="$set('tab', 'meeting_attendance')" @if(!$selectedMeetingId) disabled @endif class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'meeting_attendance' ? 'border-blue-900 text-blue-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} {{ !$selectedMeetingId ? 'opacity-50 cursor-not-allowed' : '' }}">Meeting Attendance</button>
                <button wire:click="$set('tab', 'meeting_documents')" @if(!$selectedMeetingId) disabled @endif class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'meeting_documents' ? 'border-blue-900 text-blue-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} {{ !$selectedMeetingId ? 'opacity-50 cursor-not-allowed' : '' }}">Meeting Documents</button>
            </nav>
        </div>

        <div>
            @if($tab === 'committees')
                <livewire:profile-setting.committee-manager />
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Select a Committee to Manage</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($committees as $committee)
                            <button wire:click="selectCommittee({{ $committee->id }})" class="px-4 py-2 rounded-lg border border-blue-900 text-blue-900 bg-white hover:bg-blue-50 {{ $selectedCommitteeId === $committee->id ? 'bg-blue-900 text-white' : '' }}">
                                {{ $committee->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif($tab === 'committee_members' && $selectedCommitteeId)
                <livewire:profile-setting.committee-members-manager :committee_id="$selectedCommitteeId" />
            @elseif($tab === 'meetings' && $selectedCommitteeId)
                <livewire:profile-setting.meeting-manager :committee_id="$selectedCommitteeId" />
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Select a Meeting to Manage Attendance or Documents</h3>
                    @php
                        $meetings = \App\Models\Meeting::where('committee_id', $selectedCommitteeId)->orderByDesc('meeting_date')->get();
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($meetings as $meeting)
                            <button wire:click="selectMeeting({{ $meeting->id }})" class="px-4 py-2 rounded-lg border border-blue-900 text-blue-900 bg-white hover:bg-blue-50 {{ $selectedMeetingId === $meeting->id ? 'bg-blue-900 text-white' : '' }}">
                                {{ $meeting->title }} ({{ $meeting->meeting_date ? \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y H:i') : '-' }})
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif($tab === 'meeting_attendance' && $selectedMeetingId)
                <livewire:profile-setting.meeting-attendance-manager :meeting_id="$selectedMeetingId" />
            @elseif($tab === 'meeting_documents' && $selectedMeetingId)
                <livewire:profile-setting.meeting-documents-manager :meeting_id="$selectedMeetingId" />
            @else
                <div class="text-center text-gray-400 py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>Select a tab and context to begin.</div>
                </div>
            @endif
        </div>
    </div>
</div> 