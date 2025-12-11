@props([
    'canView' => false,
    'canEdit' => false,
    'canDelete' => false,
    'canCreate' => false,
    'canApprove' => false,
    'canReject' => false
])

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold mb-4">Branches List</h2>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($this->columns() as $column)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ $column->label }}
                            </th>
                        @endforeach
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($branches as $branch)
                        <tr>
                            @foreach($this->columns() as $column)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $branch->{$column->name} }}
                                </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if($canView)
                                    <button wire:click="viewBranch({{ $branch->id }})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @endif
                                    @if($canEdit)
                                    <button wire:click="editBranchModal({{ $branch->id }})" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    @if($canDelete)
                                    <button wire:click="blockBranchModal({{ $branch->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($this->columns()) + 1 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                No branches found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $branches->links() }}
        </div>
    </div>
</div>
