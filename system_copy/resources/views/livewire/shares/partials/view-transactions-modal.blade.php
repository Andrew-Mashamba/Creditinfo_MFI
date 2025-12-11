<!-- View Transactions Modal -->
<div class="modal" style="display: {{ $showViewTransactions ? 'block' : 'none' }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Account Transactions</h5>
                <button type="button" class="close" wire:click="$set('showViewTransactions', false)">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                @if (session()->has('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search transactions..." wire:model.debounce.300ms="search">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-primary" wire:click="exportTransactions">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Shares</th>
                                <th>Price</th>
                                <th>Total Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($transactions) && $transactions->count() > 0)
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $transaction->type === 'purchase' ? 'success' : 'danger' }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->reference_number }}</td>
                                        <td>{{ number_format($transaction->shares) }}</td>
                                        <td>{{ number_format($transaction->price_per_share, 2) }}</td>
                                        <td>{{ number_format($transaction->total_value, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" wire:click="viewTransactionDetails({{ $transaction->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center">No transactions found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(isset($transactions) && $transactions->hasPages())
                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showViewTransactions', false)">Close</button>
            </div>
        </div>
    </div>
</div> 