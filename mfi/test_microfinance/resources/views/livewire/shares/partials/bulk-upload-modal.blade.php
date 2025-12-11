<!-- Bulk Upload Modal -->
<div class="modal" style="display: {{ $showBulkUpload ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Upload Share Transactions</h5>
                <button type="button" class="close" wire:click="$set('showBulkUpload', false)">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="bulkUpload">
                    <div class="form-group">
                        <label for="upload_file">Upload File (CSV/Excel)</label>
                        <input type="file" class="form-control-file" id="upload_file" wire:model="uploadFile">
                        @error('uploadFile')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="alert alert-info">
                        <strong>Instructions:</strong>
                        <ul>
                            <li>Accepted formats: .csv, .xlsx</li>
                            <li>Required columns: Member ID, Account Number, Shares, Price per Share, Transaction Type, Date</li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="bulkUpload">Upload</span>
                        <span wire:loading wire:target="bulkUpload"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> 