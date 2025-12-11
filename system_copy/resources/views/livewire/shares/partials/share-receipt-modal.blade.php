<!-- Share Receipt Modal -->
<div class="modal fade @if($showShareReceiptModal) show @endif" 
     style="display: {{ $showShareReceiptModal ? 'block' : 'none' }};" 
     tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-receipt"></i> Transaction Receipt
                </h5>
                <button type="button" class="close text-white" wire:click="closeShareReceiptModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body" id="shareReceiptContent">
                @if($shareReceiptData)
                <!-- Receipt Header -->
                <div class="text-center mb-4">
                    <h4 class="font-weight-bold">SACCOS MANAGEMENT SYSTEM</h4>
                    <p class="mb-1">{{ $shareReceiptData['branch'] ?? 'Main Branch' }}</p>
                    <p class="mb-0">{{ $shareReceiptData['transaction_type'] }} Receipt</p>
                </div>
                
                <hr>
                
                <!-- Receipt Details -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Receipt No:</strong> {{ $shareReceiptData['receipt_number'] }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ $shareReceiptData['transaction_date'] }}</p>
                        <p class="mb-1"><strong>Reference:</strong> {{ $shareReceiptData['reference_number'] }}</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <p class="mb-1"><strong>Member No:</strong> {{ $shareReceiptData['member_number'] }}</p>
                        <p class="mb-1"><strong>Member Name:</strong> {{ $shareReceiptData['member_name'] }}</p>
                    </div>
                </div>
                
                <hr>
                
                <!-- Transaction Details -->
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title font-weight-bold">Transaction Details</h6>
                        
                        @if(isset($shareReceiptData['shares_purchased']))
                        <!-- Share Purchase Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Product:</strong> {{ $shareReceiptData['share_product'] }}</p>
                                <p class="mb-1"><strong>Shares Purchased:</strong> {{ $shareReceiptData['shares_purchased'] }}</p>
                                <p class="mb-1"><strong>Price per Share:</strong> TZS {{ $shareReceiptData['price_per_share'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success mb-0">
                                    <h5 class="mb-1">Total Amount</h5>
                                    <h4 class="mb-0"><strong>TZS {{ $shareReceiptData['total_amount'] }}</strong></h4>
                                </div>
                            </div>
                        </div>
                        @elseif(isset($shareReceiptData['shares_redeemed']))
                        <!-- Share Redemption Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Product:</strong> {{ $shareReceiptData['share_product'] }}</p>
                                <p class="mb-1"><strong>Shares Redeemed:</strong> {{ $shareReceiptData['shares_redeemed'] }}</p>
                                <p class="mb-1"><strong>Reason:</strong> {{ $shareReceiptData['reason'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0">
                                    <h5 class="mb-1">Redemption Value</h5>
                                    <h4 class="mb-0"><strong>TZS {{ $shareReceiptData['total_amount'] }}</strong></h4>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <hr class="my-2">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <p class="mb-1"><strong>Payment Method:</strong> {{ $shareReceiptData['payment_method'] }}</p>
                                <p class="mb-0"><strong>Processed By:</strong> {{ $shareReceiptData['processed_by'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Receipt Footer -->
                <div class="mt-4 text-center">
                    <p class="mb-1 text-muted"><small>This is a computer-generated receipt and does not require a signature.</small></p>
                    <p class="mb-0 text-muted"><small>Thank you for your transaction!</small></p>
                </div>
                
                <!-- Signature Section (for printed receipts) -->
                <div class="row mt-4 d-none d-print-block">
                    <div class="col-md-6">
                        <div class="border-top pt-2">
                            <p class="mb-0">Member Signature</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-top pt-2">
                            <p class="mb-0">Officer Signature</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printShareReceipt()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button type="button" class="btn btn-success" wire:click="downloadShareReceipt">
                    <i class="fas fa-download"></i> Download PDF
                </button>
                <button type="button" class="btn btn-secondary" wire:click="closeShareReceiptModal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

@if($showShareReceiptModal)
<div class="modal-backdrop fade show"></div>
@endif

<!-- Print Styles and Script -->
<style nonce="{{ csp_nonce() }}">
@media print {
    body * {
        visibility: hidden;
    }
    
    #shareReceiptContent, #shareReceiptContent * {
        visibility: visible;
    }
    
    #shareReceiptContent {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
    }
    
    .modal-header, .modal-footer, .btn {
        display: none !important;
    }
    
    .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
    }
    
    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }
    
    .modal-backdrop {
        display: none !important;
    }
    
    .d-print-block {
        display: block !important;
    }
}
</style>

<script nonce="{{ csp_nonce() }}">
function printShareReceipt() {
    window.print();
    
    // Update printed_at timestamp
    @this.call('markReceiptAsPrinted');
}

// Listen for print event from Livewire
window.addEventListener('printShareReceipt', event => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>