<!-- Adjust Balance Modal -->
<div class="modal" style="display: {{ $showAdjustBalance ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Share Account Balance</h5>
                <button type="button" class="close" wire:click="$set('showAdjustBalance', false)">
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

                <form wire:submit.prevent="adjustBalance">
                    <div class="form-group">
                        <label for="account">Select Account</label>
                        <select class="form-control" id="account" wire:model="selectedAccountId">
                            <option value="">Select Account</option>
                            @foreach($shareAccounts ?? [] as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->account_name }} ({{ $account->account_number }})
                                    - Current Balance: {{ number_format($account->total_value ?? 0, 2) }} TZS
                                </option>
                            @endforeach
                        </select>
                        @error('selectedAccountId')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($selectedAccountId)
                        <div class="form-group">
                            <label for="adjustment_type">Adjustment Type</label>
                            <select class="form-control" id="adjustment_type" wire:model="adjustmentType">
                                <option value="">Select Type</option>
                                <option value="increase">Increase Balance</option>
                                <option value="decrease">Decrease Balance</option>
                            </select>
                            @error('adjustmentType')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="shares">Number of Shares</label>
                            <input type="number" class="form-control" id="shares" wire:model="shares">
                            @error('shares')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price_per_share">Price per Share (TZS)</label>
                            <input type="number" step="0.01" class="form-control" id="price_per_share" 
                                   wire:model="pricePerShare">
                            @error('pricePerShare')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($shares && $pricePerShare)
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between">
                                    <span>Total Value:</span>
                                    <span class="font-weight-bold">
                                        {{ number_format($shares * $pricePerShare, 2) }} TZS
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="narration">Narration</label>
                            <textarea class="form-control" id="narration" wire:model="narration" rows="3"></textarea>
                            @error('narration')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Confirm Password</label>
                            <input type="password" class="form-control" id="password" wire:model="password">
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showAdjustBalance', false)">Cancel</button>
                @if($selectedAccountId)
                    <button type="button" class="btn btn-primary" wire:click="adjustBalance" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="adjustBalance">Adjust Balance</span>
                        <span wire:loading wire:target="adjustBalance">
                            <i class="fas fa-spinner fa-spin"></i> Processing...
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div> 