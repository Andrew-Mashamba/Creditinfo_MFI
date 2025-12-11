<!-- Pending Transactions Modal -->
<div class="modal fade" id="pendingTransactionsModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pending Transactions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" wire:model.debounce.300ms="search" 
                           placeholder="Search transactions...">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Shares</th>
                                <th>Price</th>
                                <th>Total Value</th>
                                <th>Posted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->pendingTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        {{ $transaction->share->member->first_name }} 
                                        {{ $transaction->share->member->last_name }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->transaction_type === 'purchase' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->transaction_type) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($transaction->shares) }}</td>
                                    <td>{{ number_format($transaction->price_per_share, 2) }} TZS</td>
                                    <td>{{ number_format($transaction->total_value, 2) }} TZS</td>
                                    <td>{{ $transaction->postedBy->name }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-success" 
                                                    wire:click="approveTransaction({{ $transaction->id }})"
                                                    wire:loading.attr="disabled">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    wire:click="rejectTransaction({{ $transaction->id }})"
                                                    wire:loading.attr="disabled">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" 
                                                    wire:click="viewTransactionDetails({{ $transaction->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">No pending transactions found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $this->pendingTransactions->links() }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" wire:click="exportPendingTransactions">
                    <i class="fas fa-download mr-2"></i> Export
                </button>
            </div>
        </div>
    </div>
</div> 