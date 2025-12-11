<!-- SMS Settings Modal -->
<div class="modal" style="display: {{ $showSmsSettings ? 'block' : 'none' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Settings</h5>
                <button type="button" class="close" wire:click="$set('showSmsSettings', false)">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="saveSmsSettings">
                    <div class="form-group">
                        <label for="sender_name">Sender Name</label>
                        <input type="text" class="form-control" id="sender_name" wire:model="smsSenderName">
                        @error('smsSenderName')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="api_key">API Key</label>
                        <input type="text" class="form-control" id="api_key" wire:model="smsApiKey">
                        @error('smsApiKey')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="sms_enabled" wire:model="smsEnabled">
                        <label class="form-check-label" for="sms_enabled">Enable SMS Notifications</label>
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveSmsSettings">Save Settings</span>
                        <span wire:loading wire:target="saveSmsSettings"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> 