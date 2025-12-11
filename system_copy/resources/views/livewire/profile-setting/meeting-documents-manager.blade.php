<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mt-4">
    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Documents for {{ $meeting->title }}
    </h2>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif
    <form wire:submit.prevent="uploadDocument" class="flex flex-col md:flex-row gap-2 items-center mb-6">
        <input type="file" wire:model="file" class="w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800">Upload</button>
        @error('file') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $doc)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doc->file_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $doc->created_at ? $doc->created_at->format('M d, Y H:i') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ Storage::disk('public')->url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-semibold mr-2">Download</a>
                            <button wire:click="deleteDocument({{ $doc->id }})" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>No documents found for this meeting.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $documents->links() }}
        </div>
    </div>
</div> 