<!-- Issue Shares Modal -->
<div class="modal" style="display: {{ $showIssueNewShares ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Issue New Shares</h5>
                <button type="button" class="close" wire:click="$set('showIssueNewShares', false)">
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

                <form wire:submit.prevent="issueShares">
                    <div class="form-group">
                        <label for="member">Select Member</label>
                        <select class="form-control" id="member" wire:model.defer="member">
                            <option value="">Select Member</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">
                                    {{ $member->first_name }} {{ $member->last_name }} ({{ $member->client_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('member')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($member)
                        <div class="form-group">
                            <label for="share_account">Share Account</label>
                            <select class="form-control" id="share_account" wire:model.defer="share_account">
                                <option value="">Select Share Account</option>
                                @foreach($shareAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                @endforeach
                            </select>
                            @error('share_account')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Price per share</span>
                                <span>{{ number_format($currentShareValue, 2) }} TZS</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total shares</span>
                                <span>{{ number_format($totalShares) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Current user shares</span>
                                <span class="{{ $shares_limit_exceeded ? 'text-danger' : '' }}">
                                    {{ number_format($userShares) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Available shares</span>
                                <span class="{{ $shares_limit_exceeded ? 'text-danger' : '' }}">
                                    {{ $shares_limit_exceeded ? 'Exceeded limit' : number_format($sharesAvailable) }}
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="number_of_shares">Number of Shares</label>
                            <input type="number" class="form-control" id="number_of_shares" 
                                   min="0" max="{{ $sharesAvailable }}" 
                                   wire:model.defer="number_of_shares">
                            @error('number_of_shares')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="linked_savings_account">Linked Savings Account</label>
                            <select class="form-control" id="linked_savings_account" wire:model.defer="linked_savings_account">
                                <option value="">Select Savings Account</option>
                                @foreach($savingsAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->account_name }} (Balance: {{ number_format($account->balance, 2) }} TZS)
                                    </option>
                                @endforeach
                            </select>
                            @error('linked_savings_account')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showIssueNewShares', false)">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="issueShares" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="issueShares">Issue Shares</span>
                    <span wire:loading wire:target="issueShares">
                        <i class="fas fa-spinner fa-spin"></i> Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div> 