{{-- CLIENT'S INFORMATION SECTION --}}
<div class="space-y-4">
    <!-- Basic Information -->
    <div>
        <h4 class="text-xs font-medium text-gray-700 mb-2">Basic Information</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Full Name</div>
                <div class="font-semibold text-blue-900">{{ $basicInfo['full_name'] ?? 'N/A' }}</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Client Number</div>
                <div class="font-semibold text-blue-900">{{ $basicInfo['client_number'] ?? 'N/A' }}</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Date of Birth</div>
                <div class="font-semibold">{{ $basicInfo['date_of_birth'] ?? 'N/A' }}</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Age</div>
                <div class="font-semibold">{{ $basicInfo['age'] ?? 'N/A' }} years</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Gender</div>
                <div class="font-semibold">{{ $basicInfo['gender'] ?? 'N/A' }}</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Marital Status</div>
                <div class="font-semibold">{{ $basicInfo['marital_status'] ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div>
        <h4 class="text-xs font-medium text-gray-700 mb-2">Contact Information</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Phone Number</div>
                <div class="font-semibold text-blue-900">{{ $contactInfo['phone_number'] ?? 'N/A' }}</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Email</div>
                <div class="font-semibold text-blue-900">{{ $contactInfo['email'] ?? 'N/A' }}</div>
            </div>
            @if(($contactInfo['address'] ?? 'N/A') !== 'N/A')
            <div class="p-2 border border-gray-200 rounded col-span-2">
                <div class="text-gray-600">Address</div>
                <div class="font-semibold">{{ $contactInfo['address'] ?? 'N/A' }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Financial Information -->
    <div>
        <h4 class="text-xs font-medium text-gray-700 mb-2">Financial Information</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Total Savings</div>
                <div class="font-semibold text-blue-900">{{ number_format($financialInfo['savings_balance'] ?? 0, 2) }} TZS</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Shares</div>
                <div class="font-semibold text-blue-900">{{ number_format($financialInfo['shares'] ?? 0, 2) }} TZS</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Active Loans</div>
                <div class="font-semibold">{{ $financialInfo['active_loans_count'] ?? 0 }}</div>
            </div>
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Available Income</div>
                <div class="font-semibold text-blue-900">{{ number_format($financialInfo['income_available'] ?? 0, 2) }} TZS</div>
            </div>
        </div>
    </div>

    <!-- Employment Information (only if available) -->
    @if(($employmentInfo['employment_status'] ?? 'N/A') !== 'N/A' || ($employmentInfo['basic_salary'] ?? 0) > 0)
    <div>
        <h4 class="text-xs font-medium text-gray-700 mb-2">Employment Information</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
            @if(($employmentInfo['employment_status'] ?? 'N/A') !== 'N/A')
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Employment Status</div>
                <div class="font-semibold">{{ $employmentInfo['employment_status'] ?? 'N/A' }}</div>
            </div>
            @endif
            @if(($employmentInfo['occupation'] ?? 'N/A') !== 'N/A')
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Occupation</div>
                <div class="font-semibold">{{ $employmentInfo['occupation'] ?? 'N/A' }}</div>
            </div>
            @endif
            @if(($employmentInfo['basic_salary'] ?? 0) > 0)
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Basic Salary</div>
                <div class="font-semibold text-blue-900">{{ number_format($employmentInfo['basic_salary'] ?? 0, 2) }} TZS</div>
            </div>
            @endif
            @if(($employmentInfo['annual_income'] ?? 0) > 0)
            <div class="p-2 border border-gray-200 rounded">
                <div class="text-gray-600">Annual Income</div>
                <div class="font-semibold text-blue-900">{{ number_format($employmentInfo['annual_income'] ?? 0, 2) }} TZS</div>
            </div>
            @endif
        </div>
    </div>
    @endif


</div>

