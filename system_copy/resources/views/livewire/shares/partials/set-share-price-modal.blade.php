<!-- Set Share Price Modal -->
<div class="modal" style="display: {{ $showSetSharePrice ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Share Price</h5>
                <button type="button" class="close" wire:click="$set('showSetSharePrice', false)">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="setSharePrice">
                    <div class="form-group">
                        <label for="new_price">New Price (TZS)</label>
                        <input type="number" step="0.01" class="form-control" id="new_price" wire:model="newSharePrice">
                        @error('newSharePrice')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="effective_date">Effective Date</label>
                        <input type="date" class="form-control" id="effective_date" wire:model="effectiveDate">
                        @error('effectiveDate')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="setSharePrice">Set Price</span>
                        <span wire:loading wire:target="setSharePrice"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> 