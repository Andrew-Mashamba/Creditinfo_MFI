<!-- Share Accounts Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Share Accounts</h5>
            <div class="d-flex align-items-center">
                <div class="input-group mr-3">
                    <input type="text" class="form-control" wire:model.debounce.300ms="search" 
                           placeholder="Search accounts...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
                <button wire:click="$set('showCreateShareAccount', true)" 
                        class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    New Account
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Account Details</th>
                        <th>Member</th>
                        <th>Shares</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shareAccounts as $account)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $account->account_name }}</h6>
                                        <small class="text-muted">{{ $account->account_number }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div>{{ $account->client->first_name }} {{ $account->client->last_name }}</div>
                                    <small class="text-muted">{{ $account->client->client_number }}</small>
                                </div>
                            </td>
                            <td>
                                <div>{{ number_format($account->shares_count) }}</div>
                                <small class="text-muted">shares</small>
                            </td>
                            <td>
                                <div>{{ number_format($account->total_value, 2) }} TZS</div>
                                <small class="text-muted">{{ number_format($account->price_per_share, 2) }} TZS/share</small>
                            </td>
                            <td>
                                <span class="badge {{ $account->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <button wire:click="editAccount({{ $account->id }})" 
                                            class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="viewTransactions({{ $account->id }})"
                                            class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="deleteAccount({{ $account->id }})"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">No share accounts found</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $shareAccounts->links() }}
        </div>
    </div>
</div> 