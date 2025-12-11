<!-- Edit Share Account Modal -->
<div class="modal" style="display: {{ $showEditShareAccount ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Share Account</h5>
                <button type="button" class="close" wire:click="$set('showEditShareAccount', false)">
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

                <form wire:submit.prevent="updateShareAccount">
                    <div class="form-group">
                        <label for="edit_account_name">Account Name</label>
                        <input type="text" class="form-control" id="edit_account_name" wire:model.defer="edit_account_name">
                        @error('edit_account_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_region">Region</label>
                        <input type="text" class="form-control" id="edit_region" wire:model.defer="edit_region">
                        @error('edit_region')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_wilaya">Wilaya</label>
                        <input type="text" class="form-control" id="edit_wilaya" wire:model.defer="edit_wilaya">
                        @error('edit_wilaya')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_membership_number">Membership Number</label>
                        <input type="text" class="form-control" id="edit_membership_number" wire:model.defer="edit_membership_number" readonly>
                        @error('edit_membership_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_parent_share_account">Parent Share Account</label>
                        <select class="form-control" id="edit_parent_share_account" wire:model.defer="edit_parent_share_account">
                            <option value="">Select Parent Account</option>
                            @foreach($parentAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        @error('edit_parent_share_account')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" wire:model.defer="edit_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="frozen">Frozen</option>
                        </select>
                        @error('edit_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showEditShareAccount', false)">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="updateShareAccount" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateShareAccount">Update Account</span>
                    <span wire:loading wire:target="updateShareAccount">
                        <i class="fas fa-spinner fa-spin"></i> Updating...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div> 