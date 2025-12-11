<!-- Delete Share Account Modal -->
<div class="modal" style="display: {{ $showDeleteShareAccount ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Share Account</h5>
                <button type="button" class="close" wire:click="$set('showDeleteShareAccount', false)">
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

                @if($selectedAccount)
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Warning</h5>
                        <p>Are you sure you want to delete this share account? This action cannot be undone.</p>
                        <p><strong>Account Details:</strong></p>
                        <ul>
                            <li>Account Name: {{ $selectedAccount->account_name }}</li>
                            <li>Membership Number: {{ $selectedAccount->membership_number }}</li>
                            <li>Current Balance: {{ number_format($selectedAccount->balance, 2) }} TZS</li>
                            <li>Total Shares: {{ number_format($selectedAccount->shares_count) }}</li>
                            <li>Total Value: {{ number_format($selectedAccount->total_value, 2) }} TZS</li>
                        </ul>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <p>No account selected for deletion.</p>
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteShareAccount', false)">Cancel</button>
                @if($selectedAccount)
                    <button type="button" class="btn btn-danger" wire:click="deleteShareAccount" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteShareAccount">Delete Account</span>
                        <span wire:loading wire:target="deleteShareAccount">
                            <i class="fas fa-spinner fa-spin"></i> Deleting...
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div> 