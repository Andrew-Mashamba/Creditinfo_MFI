<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">SACCOS Organizational Structure</h1>
                    <p class="text-gray-600 mt-1">Tanzania Context - Governance, Management & Operations</p>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="flex items-center space-x-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Total Roles</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($totalRoles ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Departments</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($totalDepartments ?? 0) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Permissions</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($totalPermissions ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizational Structure -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- I. GOVERNANCE LEVEL -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-xl font-bold text-white">I. GOVERNANCE LEVEL</h2>
                        <p class="text-purple-100 text-sm">Elected by Members at AGM</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- AGM -->
                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="font-semibold text-gray-900">1. Annual General Meeting (AGM)</h3>
                    <p class="text-sm text-gray-600">Supreme authority of the SACCOS</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Supreme Authority
                        </span>
                    </div>
                </div>

                <!-- Board of Directors -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-semibold text-gray-900">2. Board of Directors (BOD)</h3>
                    <p class="text-sm text-gray-600">Elected governing body</p>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Chairperson</div>
                        <div class="text-xs text-gray-500">• Vice Chairperson</div>
                        <div class="text-xs text-gray-500">• Secretary</div>
                        <div class="text-xs text-gray-500">• Treasurer</div>
                        <div class="text-xs text-gray-500">• Additional Members</div>
                    </div>
                </div>

                <!-- Board Sub-Committees -->
                <div class="border-l-4 border-green-500 pl-4">
                    <h3 class="font-semibold text-gray-900">3. Board Sub-Committees</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Audit & Risk Committee</div>
                        <div class="text-xs text-gray-500">• Finance, Budget & Investment</div>
                        <div class="text-xs text-gray-500">• Credit Committee</div>
                        <div class="text-xs text-gray-500">• Education & Training</div>
                        <div class="text-xs text-gray-500">• ICT & Innovation</div>
                        <div class="text-xs text-gray-500">• Ethics, Governance & Disciplinary</div>
                    </div>
                </div>

                <!-- Supervisory Committee -->
                <div class="border-l-4 border-red-500 pl-4">
                    <h3 class="font-semibold text-gray-900">4. Supervisory Committee</h3>
                    <p class="text-sm text-gray-600">Independent oversight body</p>
                </div>

                <!-- External Auditor -->
                <div class="border-l-4 border-yellow-500 pl-4">
                    <h3 class="font-semibold text-gray-900">5. External Auditor</h3>
                    <p class="text-sm text-gray-600">Statutory financial audits</p>
                </div>
            </div>
        </div>

        <!-- II. MANAGEMENT LEVEL -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-xl font-bold text-white">II. MANAGEMENT LEVEL</h2>
                        <p class="text-gray-500 text-sm">Appointed by Board of Directors</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-semibold text-gray-900">General Manager / CEO</h3>
                    <p class="text-sm text-gray-600">Reports to the Board</p>
                    <p class="text-sm text-gray-600 mt-1">Responsible for day-to-day operations and strategy execution</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Executive Leadership
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- III. KEY DEPARTMENTS -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-xl font-bold text-white">III. KEY DEPARTMENTS</h2>
                        <p class="text-green-100 text-sm">Under Management</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Operations -->
                <div class="border-l-4 border-green-500 pl-4">
                    <h3 class="font-semibold text-gray-900">1. Operations Department</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Operations Manager</div>
                        <div class="text-xs text-gray-500">• Branch Management</div>
                        <div class="text-xs text-gray-500">• Front Desk / Member Services</div>
                        <div class="text-xs text-gray-500">• Tellers / Cashiers</div>
                        <div class="text-xs text-gray-500">• Credit Officers</div>
                        <div class="text-xs text-gray-500">• IT & Systems</div>
                    </div>
                </div>

                <!-- Finance -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-semibold text-gray-900">2. Finance & Accounts</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Finance Manager</div>
                        <div class="text-xs text-gray-500">• Financial Reporting</div>
                        <div class="text-xs text-gray-500">• Budgeting & Cash Flow</div>
                        <div class="text-xs text-gray-500">• Reconciliations</div>
                    </div>
                </div>

                <!-- Credit -->
                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="font-semibold text-gray-900">3. Credit & Recovery</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Credit Manager</div>
                        <div class="text-xs text-gray-500">• Loan Appraisal</div>
                        <div class="text-xs text-gray-500">• Portfolio Management</div>
                        <div class="text-xs text-gray-500">• Recovery & Collections</div>
                    </div>
                </div>

                <!-- Risk -->
                <div class="border-l-4 border-red-500 pl-4">
                    <h3 class="font-semibold text-gray-900">4. Risk & Compliance</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Compliance Officer</div>
                        <div class="text-xs text-gray-500">• Regulatory Compliance</div>
                        <div class="text-xs text-gray-500">• AML/CFT Monitoring</div>
                        <div class="text-xs text-gray-500">• Risk Assessments</div>
                    </div>
                </div>

                <!-- HR -->
                <div class="border-l-4 border-yellow-500 pl-4">
                    <h3 class="font-semibold text-gray-900">5. HR & Administration</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• HR & Admin Officer</div>
                        <div class="text-xs text-gray-500">• Recruitment & Training</div>
                        <div class="text-xs text-gray-500">• Staff Records</div>
                        <div class="text-xs text-gray-500">• Office Facilities</div>
                    </div>
                </div>

                <!-- Marketing -->
                <div class="border-l-4 border-indigo-500 pl-4">
                    <h3 class="font-semibold text-gray-900">6. Marketing & Member Relations</h3>
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-500">• Member Services Officer</div>
                        <div class="text-xs text-gray-500">• Business Development</div>
                        <div class="text-xs text-gray-500">• Branding & Communication</div>
                        <div class="text-xs text-gray-500">• Community Outreach</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Details -->
    <div class="mt-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 p-6">
                <h2 class="text-xl font-bold text-white">Department Details & Roles</h2>
                <p class="text-gray-100 text-sm">Comprehensive view of all departments and their roles</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($departments ?? [] as $department)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">{{ $department->department_name }}</h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $department->department_code }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($department->description, 100) }}</p>
                        
                        @if($department->roles && $department->roles->count() > 0)
                        <div class="space-y-1">
                            <h4 class="text-xs font-medium text-gray-700 uppercase tracking-wider">Roles ({{ $department->roles->count() }})</h4>
                            @foreach($department->roles->take(3) as $role)
                            <div class="text-xs text-gray-600">• {{ $role->name }}</div>
                            @endforeach
                            @if($department->roles->count() > 3)
                            <div class="text-xs text-gray-500">+{{ $department->roles->count() - 3 }} more</div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Visualization -->
    <div class="mt-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6">
                <h2 class="text-xl font-bold text-white">Organizational Hierarchy</h2>
                <p class="text-indigo-100 text-sm">Visual representation of the SACCOS structure</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <div class="min-w-max">
                        <!-- AGM Level -->
                        <div class="flex justify-center mb-8">
                            <div class="bg-purple-100 border-2 border-purple-300 rounded-lg px-6 py-3 text-center">
                                <div class="font-bold text-purple-800">AGM (Members)</div>
                                <div class="text-xs text-purple-600">Supreme Authority</div>
                            </div>
                        </div>

                        <!-- Board & Supervisory Level -->
                        <div class="flex justify-center space-x-8 mb-8">
                            <div class="bg-blue-100 border-2 border-blue-300 rounded-lg px-4 py-2 text-center">
                                <div class="font-semibold text-blue-800">Board of Directors</div>
                                <div class="text-xs text-blue-600">Governing Body</div>
                            </div>
                            <div class="bg-red-100 border-2 border-red-300 rounded-lg px-4 py-2 text-center">
                                <div class="font-semibold text-red-800">Supervisory Committee</div>
                                <div class="text-xs text-red-600">Independent Oversight</div>
                            </div>
                        </div>

                        <!-- CEO Level -->
                        <div class="flex justify-center mb-8">
                            <div class="bg-green-100 border-2 border-green-300 rounded-lg px-6 py-3 text-center">
                                <div class="font-bold text-green-800">General Manager / CEO</div>
                                <div class="text-xs text-green-600">Executive Leadership</div>
                            </div>
                        </div>

                        <!-- Department Level -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-3 py-2 text-center">
                                <div class="font-semibold text-gray-800 text-sm">Operations</div>
                            </div>
                            <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-3 py-2 text-center">
                                <div class="font-semibold text-gray-800 text-sm">Finance</div>
                            </div>
                            <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-3 py-2 text-center">
                                <div class="font-semibold text-gray-800 text-sm">Credit</div>
                            </div>
                            <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-3 py-2 text-center">
                                <div class="font-semibold text-gray-800 text-sm">Risk</div>
                            </div>
                            <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-3 py-2 text-center">
                                <div class="font-semibold text-gray-800 text-sm">HR</div>
                            </div>
                            <div class="bg-gray-100 border-2 border-gray-300 rounded-lg px-3 py-2 text-center">
                                <div class="font-semibold text-gray-800 text-sm">Marketing</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 