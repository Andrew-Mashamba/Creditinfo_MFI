<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-holding-usd me-2"></i>Early Settlement Management
                    </h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-light btn-sm" wire:click="exportSettlements">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Search and Filters -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search by loan ID, client name or number..."
                           wire:model.debounce.300ms="searchTerm">
                </div>
                <div class="col-md-2">
                    <select class="form-control" wire:model="statusFilter">
                        <option value="all">All Status</option>
                        <option value="pending_approval">Pending Approval</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="processed">Processed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model="dateFrom" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model="dateTo" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" wire:click="$refresh">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#eligible-loans">
                        <i class="fas fa-list me-1"></i>Eligible Loans
                        <span class="badge bg-primary ms-1">{{ $this->eligibleLoans->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settlement-history">
                        <i class="fas fa-history me-1"></i>Settlement History
                        <span class="badge bg-secondary ms-1">{{ $this->settlementHistory->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#pending-approvals">
                        <i class="fas fa-clock me-1"></i>Pending Approvals
                        <span class="badge bg-warning ms-1">
                            {{ $this->settlementHistory->where('status', 'pending_approval')->count() }}
                        </span>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3">
                <!-- Eligible Loans Tab -->
                <div class="tab-pane fade show active" id="eligible-loans">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Client</th>
                                    <th>Loan Amount</th>
                                    <th>Outstanding</th>
                                    <th>Days in Arrears</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eligibleLoans as $loan)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $loan->loan_id }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $loan->client->first_name }} {{ $loan->client->last_name }}</div>
                                        <small class="text-muted">{{ $loan->client->client_number }}</small>
                                    </td>
                                    <td>{{ number_format($loan->principle, 2) }}</td>
                                    <td>
                                        @php
                                            $outstanding = DB::table('loans_schedules')
                                                ->where('loan_id', $loan->loan_id)
                                                ->where('status', '!=', 'PAID')
                                                ->sum(DB::raw('principle + interest'));
                                        @endphp
                                        <span class="fw-bold text-danger">
                                            {{ number_format($outstanding, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($loan->days_in_arrears > 0)
                                            <span class="badge bg-danger">{{ $loan->days_in_arrears }} days</span>
                                        @else
                                            <span class="badge bg-success">Current</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($loan->loan_status == 'ACTIVE')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($loan->loan_status == 'OVERDUE')
                                            <span class="badge bg-warning">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $loan->loan_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                wire:click="initiateSettlement({{ $loan->id }})">
                                            <i class="fas fa-hand-holding-usd me-1"></i>Settle
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No eligible loans found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $eligibleLoans->links() }}
                </div>

                <!-- Settlement History Tab -->
                <div class="tab-pane fade" id="settlement-history">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Settlement Date</th>
                                    <th>Loan ID</th>
                                    <th>Client</th>
                                    <th>Settlement Amount</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($settlementHistory as $settlement)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($settlement->settlement_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="fw-bold">{{ $settlement->loan_id }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $settlement->client_name }}</div>
                                        <small class="text-muted">{{ $settlement->client_number }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($settlement->settlement_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($settlement->discount_amount > 0)
                                            <span class="text-success">{{ number_format($settlement->discount_amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($settlement->status == 'pending_approval')
                                            <span class="badge bg-warning">Pending Approval</span>
                                        @elseif($settlement->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($settlement->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($settlement->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($settlement->status == 'pending_approval')
                                            <button class="btn btn-sm btn-success" 
                                                    wire:click="approveSettlement({{ $settlement->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-info" 
                                                    wire:click="viewDetails({{ $settlement->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No settlement history found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $settlementHistory->links('pagination::bootstrap-4', ['pageName' => 'historyPage']) }}
                </div>

                <!-- Pending Approvals Tab -->
                <div class="tab-pane fade" id="pending-approvals">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request Date</th>
                                    <th>Loan ID</th>
                                    <th>Client</th>
                                    <th>Outstanding</th>
                                    <th>Settlement Amount</th>
                                    <th>Discount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pendingSettlements = $settlementHistory->where('status', 'pending_approval');
                                @endphp
                                @forelse($pendingSettlements as $settlement)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($settlement->created_at)->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="fw-bold">{{ $settlement->loan_id }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $settlement->client_name }}</div>
                                        <small class="text-muted">{{ $settlement->client_number }}</small>
                                    </td>
                                    <td>
                                        {{ number_format($settlement->outstanding_principal + $settlement->outstanding_interest + $settlement->penalty_amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($settlement->settlement_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($settlement->discount_amount > 0)
                                            <div>
                                                <span class="text-danger">-{{ number_format($settlement->discount_amount, 2) }}</span>
                                                @if($settlement->discount_rate > 0)
                                                    <small class="d-block">Interest: {{ $settlement->discount_rate }}%</small>
                                                @endif
                                                @if($settlement->waiver_rate > 0)
                                                    <small class="d-block">Penalty: {{ $settlement->waiver_rate }}%</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success" 
                                                wire:click="approveSettlement({{ $settlement->id }})"
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info" 
                                                wire:click="viewDetails({{ $settlement->id }})"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">
                                        <i class="fas fa-check-circle me-1"></i>No pending approvals
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Modal -->
    @if($showSettlementModal && $selectedLoan)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-hand-holding-usd me-2"></i>Early Settlement - {{ $selectedLoan->loan_id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeSettlementModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Client Information</label>
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <div>{{ $selectedLoan->client->first_name }} {{ $selectedLoan->client->last_name }}</div>
                                    <small class="text-muted">{{ $selectedLoan->client->client_number }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loan Details</label>
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <div>Amount: {{ number_format($selectedLoan->principle, 2) }}</div>
                                    <small class="text-muted">{{ $selectedLoan->product->sub_product_name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Settlement Date</label>
                            <input type="date" class="form-control" wire:model="settlementDate">
                            @error('settlementDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" wire:model="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            @error('paymentMethod') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Interest Discount (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   wire:model.lazy="discountRate"
                                   wire:change="calculateSettlementAmount"
                                   min="0" 
                                   max="100" 
                                   step="0.01">
                            @error('discountRate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Penalty Waiver (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   wire:model.lazy="waiverRate"
                                   wire:change="calculateSettlementAmount"
                                   min="0" 
                                   max="100" 
                                   step="0.01">
                            @error('waiverRate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if($paymentMethod !== 'cash')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" class="form-control" wire:model="paymentReference">
                            @error('paymentReference') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receipt Number</label>
                            <input type="text" class="form-control" wire:model="receiptNumber">
                            @error('receiptNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Settlement Reason</label>
                        <textarea class="form-control" 
                                  rows="2" 
                                  wire:model="settlementReason"
                                  placeholder="Explain the reason for early settlement..."></textarea>
                        @error('settlementReason') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" 
                                  rows="2" 
                                  wire:model="settlementNotes"
                                  placeholder="Any additional notes..."></textarea>
                        @error('settlementNotes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <hr>

                    <!-- Settlement Calculation Summary -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Settlement Calculation</h6>
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Outstanding Principal:</td>
                                    <td class="text-end">{{ number_format($outstandingPrincipal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Outstanding Interest:</td>
                                    <td class="text-end">{{ number_format($outstandingInterest, 2) }}</td>
                                </tr>
                                @if($penaltyAmount > 0)
                                <tr>
                                    <td>Penalties:</td>
                                    <td class="text-end">{{ number_format($penaltyAmount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Total Outstanding:</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($outstandingPrincipal + $outstandingInterest + $penaltyAmount, 2) }}
                                    </td>
                                </tr>
                                @if($discountAmount > 0)
                                <tr class="text-success">
                                    <td>Less: Discount/Waiver:</td>
                                    <td class="text-end">-{{ number_format($discountAmount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td class="fw-bold">Settlement Amount:</td>
                                    <td class="text-end fw-bold fs-5">
                                        {{ number_format($settlementAmount, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeSettlementModal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="processSettlement">
                        <i class="fas fa-check me-1"></i>Submit for Approval
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Approval Modal -->
    @if($showApprovalModal && $settlementToApprove)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-gavel me-2"></i>Settlement Approval
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeApprovalModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Loan ID:</strong> {{ $settlementToApprove->loan_id }}<br>
                        <strong>Settlement Amount:</strong> {{ number_format($settlementToApprove->settlement_amount, 2) }}<br>
                        @if($settlementToApprove->discount_amount > 0)
                        <strong>Discount Applied:</strong> {{ number_format($settlementToApprove->discount_amount, 2) }}
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Approval Notes</label>
                        <textarea class="form-control" 
                                  rows="3" 
                                  wire:model="approvalNotes"
                                  placeholder="Add approval notes..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rejection Reason (if rejecting)</label>
                        <textarea class="form-control" 
                                  rows="3" 
                                  wire:model="rejectionReason"
                                  placeholder="Explain rejection reason..."></textarea>
                        @error('rejectionReason') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" wire:click="rejectSettlement">
                        <i class="fas fa-times me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success" wire:click="confirmApproval">
                        <i class="fas fa-check me-1"></i>Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>