<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield me-2"></i>Guarantors Management
                    </h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-light btn-sm" wire:click="exportGuarantors">
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
                           placeholder="Search loans, clients, guarantors..."
                           wire:model.debounce.300ms="searchTerm">
                </div>
                <div class="col-md-3">
                    <select class="form-control" wire:model="typeFilter">
                        <option value="all">All Types</option>
                        <option value="individual">Individual</option>
                        <option value="group">Group</option>
                        <option value="collateral">Collateral</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" wire:model="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="released">Released</option>
                        <option value="expired">Expired</option>
                    </select>
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
                    <a class="nav-link active" data-bs-toggle="tab" href="#loans-needing">
                        <i class="fas fa-exclamation-triangle me-1"></i>Loans Needing Guarantors
                        <span class="badge bg-warning ms-1">{{ $this->loansNeedingGuarantors->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#loans-with">
                        <i class="fas fa-check-circle me-1"></i>Loans with Guarantors
                        <span class="badge bg-success ms-1">{{ $this->loansWithGuarantors->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#all-guarantors">
                        <i class="fas fa-users me-1"></i>All Guarantors
                        <span class="badge bg-primary ms-1">{{ $this->allGuarantors->total() }}</span>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3">
                <!-- Loans Needing Guarantors -->
                <div class="tab-pane fade show active" id="loans-needing">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Client</th>
                                    <th>Loan Amount</th>
                                    <th>Product</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loansNeedingGuarantors as $loan)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $loan->loan_id }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $loan->client->first_name }} {{ $loan->client->last_name }}</div>
                                        <small class="text-muted">{{ $loan->client->client_number }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($loan->principle, 2) }}</span>
                                    </td>
                                    <td>{{ $loan->product->sub_product_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($loan->loan_status == 'PENDING')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($loan->loan_status == 'APPROVED')
                                            <span class="badge bg-info">Approved</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                wire:click="addGuarantor({{ $loan->id }})">
                                            <i class="fas fa-user-plus me-1"></i>Add Guarantor
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No loans require guarantors
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $loansNeedingGuarantors->links('pagination::bootstrap-4', ['pageName' => 'needingPage']) }}
                </div>

                <!-- Loans with Guarantors -->
                <div class="tab-pane fade" id="loans-with">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Client</th>
                                    <th>Loan Amount</th>
                                    <th>Guarantors</th>
                                    <th>Coverage</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loansWithGuarantors as $loan)
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
                                            $guarantorCount = $loan->guarantors()->where('status', 'active')->count();
                                        @endphp
                                        <span class="badge bg-info">{{ $guarantorCount }} Guarantor(s)</span>
                                    </td>
                                    <td>
                                        @php
                                            $totalCoverage = $loan->guarantors()->where('status', 'active')->sum('guaranteed_amount');
                                            $coveragePercent = ($totalCoverage / $loan->principle) * 100;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $coveragePercent >= 100 ? 'bg-success' : 'bg-warning' }}" 
                                                 style="width: {{ min($coveragePercent, 100) }}%">
                                                {{ number_format($coveragePercent, 0) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ number_format($totalCoverage, 2) }} / {{ number_format($loan->principle, 2) }}</small>
                                    </td>
                                    <td>
                                        @if($loan->loan_status == 'ACTIVE')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($loan->loan_status == 'OVERDUE')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $loan->loan_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                wire:click="viewLoanGuarantors({{ $loan->id }})">
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-sm btn-primary" 
                                                wire:click="addGuarantor({{ $loan->id }})">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No loans with guarantors found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $loansWithGuarantors->links() }}
                </div>

                <!-- All Guarantors -->
                <div class="tab-pane fade" id="all-guarantors">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Guarantor</th>
                                    <th>Type</th>
                                    <th>Loan ID</th>
                                    <th>Borrower</th>
                                    <th>Guaranteed Amount</th>
                                    <th>Status</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allGuarantors as $guarantor)
                                <tr>
                                    <td>
                                        @if($guarantor->guarantor_type == 'individual')
                                            <div>{{ $guarantor->guarantor_name }}</div>
                                            <small class="text-muted">{{ $guarantor->guarantor_id_number }}</small>
                                        @elseif($guarantor->guarantor_type == 'group')
                                            <div>{{ $guarantor->group_name }}</div>
                                            <small class="text-muted">Reg: {{ $guarantor->group_registration_number }}</small>
                                        @else
                                            <div>{{ $guarantor->collateral_type }}</div>
                                            <small class="text-muted">{{ Str::limit($guarantor->collateral_description, 30) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($guarantor->guarantor_type == 'individual')
                                            <span class="badge bg-primary">Individual</span>
                                        @elseif($guarantor->guarantor_type == 'group')
                                            <span class="badge bg-info">Group</span>
                                        @else
                                            <span class="badge bg-warning">Collateral</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $guarantor->loan_id }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $guarantor->borrower_name }}</div>
                                        <small class="text-muted">{{ $guarantor->client_number }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($guarantor->guaranteed_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($guarantor->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($guarantor->status == 'released')
                                            <span class="badge bg-secondary">Released</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($guarantor->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($guarantor->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                wire:click="viewGuarantorDetails({{ $guarantor->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($guarantor->status == 'active' && $guarantor->loan_status == 'SETTLED')
                                            <button class="btn btn-sm btn-warning" 
                                                    wire:click="initiateRelease({{ $guarantor->id }})">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle me-1"></i>No guarantors found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $allGuarantors->links('pagination::bootstrap-4', ['pageName' => 'allPage']) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Guarantor Modal -->
    @if($showAddGuarantorModal && $selectedLoan)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Add Guarantor - {{ $selectedLoan->loan_id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddGuarantorModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <small class="text-muted">Client:</small>
                                    <div class="fw-bold">{{ $selectedLoan->client->first_name }} {{ $selectedLoan->client->last_name }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body p-2">
                                    <small class="text-muted">Loan Amount:</small>
                                    <div class="fw-bold">{{ number_format($selectedLoan->principle, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Guarantor Type</label>
                        <select class="form-control" wire:model="guarantorType">
                            <option value="individual">Individual Guarantor</option>
                            <option value="group">Group Guarantor</option>
                            <option value="collateral">Collateral</option>
                        </select>
                    </div>

                    @if($guarantorType == 'individual')
                    <!-- Individual Guarantor Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" wire:model="guarantorName">
                            @error('guarantorName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID Number</label>
                            <input type="text" class="form-control" wire:model="guarantorIdNumber">
                            @error('guarantorIdNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" wire:model="guarantorPhone">
                            @error('guarantorPhone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" wire:model="guarantorEmail">
                            @error('guarantorEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Relationship</label>
                            <input type="text" class="form-control" wire:model="guarantorRelationship">
                            @error('guarantorRelationship') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" wire:model="guarantorOccupation">
                            @error('guarantorOccupation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Monthly Income</label>
                            <input type="number" class="form-control" wire:model="guarantorIncome">
                            @error('guarantorIncome') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" wire:model="guarantorAddress">
                            @error('guarantorAddress') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @elseif($guarantorType == 'group')
                    <!-- Group Guarantor Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Group Name</label>
                            <input type="text" class="form-control" wire:model="groupName">
                            @error('groupName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" class="form-control" wire:model="groupRegNumber">
                            @error('groupRegNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group Members</label>
                        <button type="button" class="btn btn-sm btn-primary ms-2" wire:click="addGroupMember">
                            <i class="fas fa-plus"></i> Add Member
                        </button>
                        @foreach($groupMembers as $index => $member)
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Name" 
                                       wire:model="groupMembers.{{ $index }}.name">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="ID Number" 
                                       wire:model="groupMembers.{{ $index }}.id_number">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Phone" 
                                       wire:model="groupMembers.{{ $index }}.phone">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" placeholder="Share %" 
                                       wire:model="groupMembers.{{ $index }}.share">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger" 
                                        wire:click="removeGroupMember({{ $index }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @else
                    <!-- Collateral Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Collateral Type</label>
                            <select class="form-control" wire:model="collateralType">
                                <option value="">Select Type</option>
                                <option value="land">Land/Property</option>
                                <option value="vehicle">Vehicle</option>
                                <option value="equipment">Equipment</option>
                                <option value="inventory">Inventory</option>
                                <option value="receivables">Receivables</option>
                                <option value="other">Other</option>
                            </select>
                            @error('collateralType') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Value</label>
                            <input type="number" class="form-control" wire:model="collateralValue">
                            @error('collateralValue') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" wire:model="collateralDescription"></textarea>
                        @error('collateralDescription') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" wire:model="collateralLocation">
                            @error('collateralLocation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valuation Date</label>
                            <input type="date" class="form-control" wire:model="valuationDate">
                            @error('valuationDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valuation Report (Optional)</label>
                        <input type="file" class="form-control" wire:model="valuationReport">
                        @error('valuationReport') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Guaranteed Amount</label>
                        <input type="number" class="form-control" wire:model="guaranteedAmount">
                        @error('guaranteedAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="2" wire:model="guarantorNotes"></textarea>
                        @error('guarantorNotes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddGuarantorModal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="saveGuarantor">
                        <i class="fas fa-save me-1"></i>Save Guarantor
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Details Modal -->
    @if($showDetailsModal && $selectedGuarantor)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Guarantor Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                </div>
                <div class="modal-body">
                    <!-- Details content based on guarantor type -->
                    @if($selectedGuarantor->guarantor_type == 'individual')
                        <h6 class="fw-bold">Individual Guarantor</h6>
                        <table class="table table-sm">
                            <tr><td>Name:</td><td>{{ $selectedGuarantor->guarantor_name }}</td></tr>
                            <tr><td>ID Number:</td><td>{{ $selectedGuarantor->guarantor_id_number }}</td></tr>
                            <tr><td>Phone:</td><td>{{ $selectedGuarantor->guarantor_phone }}</td></tr>
                            <tr><td>Email:</td><td>{{ $selectedGuarantor->guarantor_email ?? 'N/A' }}</td></tr>
                            <tr><td>Address:</td><td>{{ $selectedGuarantor->guarantor_address }}</td></tr>
                            <tr><td>Relationship:</td><td>{{ $selectedGuarantor->relationship }}</td></tr>
                            <tr><td>Occupation:</td><td>{{ $selectedGuarantor->occupation ?? 'N/A' }}</td></tr>
                            <tr><td>Monthly Income:</td><td>{{ number_format($selectedGuarantor->monthly_income ?? 0, 2) }}</td></tr>
                        </table>
                    @elseif($selectedGuarantor->guarantor_type == 'collateral')
                        <h6 class="fw-bold">Collateral</h6>
                        <table class="table table-sm">
                            <tr><td>Type:</td><td>{{ $selectedGuarantor->collateral_type }}</td></tr>
                            <tr><td>Description:</td><td>{{ $selectedGuarantor->collateral_description }}</td></tr>
                            <tr><td>Value:</td><td>{{ number_format($selectedGuarantor->collateral_value, 2) }}</td></tr>
                            <tr><td>Location:</td><td>{{ $selectedGuarantor->collateral_location }}</td></tr>
                            <tr><td>Valuation Date:</td><td>{{ $selectedGuarantor->valuation_date }}</td></tr>
                        </table>
                    @else
                        <h6 class="fw-bold">Group Guarantor</h6>
                        <table class="table table-sm">
                            <tr><td>Group Name:</td><td>{{ $selectedGuarantor->group_name }}</td></tr>
                            <tr><td>Registration Number:</td><td>{{ $selectedGuarantor->group_registration_number }}</td></tr>
                        </table>
                    @endif
                    
                    <hr>
                    <table class="table table-sm">
                        <tr><td>Loan ID:</td><td>{{ $selectedGuarantor->loan_id }}</td></tr>
                        <tr><td>Guaranteed Amount:</td><td class="fw-bold">{{ number_format($selectedGuarantor->guaranteed_amount, 2) }}</td></tr>
                        <tr><td>Status:</td><td><span class="badge bg-{{ $selectedGuarantor->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($selectedGuarantor->status) }}</span></td></tr>
                        <tr><td>Date Added:</td><td>{{ \Carbon\Carbon::parse($selectedGuarantor->created_at)->format('M d, Y H:i') }}</td></tr>
                        @if($selectedGuarantor->notes)
                        <tr><td>Notes:</td><td>{{ $selectedGuarantor->notes }}</td></tr>
                        @endif
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Release Modal -->
    @if($showReleaseModal && $selectedGuarantor)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-unlock me-2"></i>Release Guarantor
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeReleaseModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to release this guarantor?
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Release Reason</label>
                        <textarea class="form-control" rows="3" wire:model="releaseReason" 
                                  placeholder="Explain why this guarantor is being released..."></textarea>
                        @error('releaseReason') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeReleaseModal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-warning" wire:click="releaseGuarantor">
                        <i class="fas fa-unlock me-1"></i>Release Guarantor
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>