<!-- Declare Dividend Modal -->
<div class="modal" style="display: {{ $showDeclareDividend ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Declare Dividend</h5>
                <button type="button" class="close" wire:click="$set('showDeclareDividend', false)">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="declareDividend">
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" class="form-control" id="year" wire:model="dividendYear">
                        @error('dividendYear')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="rate">Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" id="rate" wire:model="dividendRate">
                        @error('dividendRate')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="amount">Total Amount (TZS)</label>
                        <input type="number" step="0.01" class="form-control" id="amount" wire:model="dividendAmount">
                        @error('dividendAmount')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="payment_mode">Payment Mode</label>
                        <select class="form-control" id="payment_mode" wire:model="dividendPaymentMode">
                            <option value="">Select Payment Mode</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="cash">Cash</option>
                        </select>
                        @error('dividendPaymentMode')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="narration">Narration</label>
                        <textarea class="form-control" id="narration" wire:model="dividendNarration"></textarea>
                        @error('dividendNarration')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="declareDividend">Declare Dividend</span>
                        <span wire:loading wire:target="declareDividend"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> 