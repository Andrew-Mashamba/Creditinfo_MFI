<div class="p-4">
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Recruitment Management</h1>
        <p class="text-gray-600">Manage job postings, applicants, interviews, and onboarding processes</p>
    </div>

    <!-- Search and Filter Section -->
    <div class="mb-6 flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" wire:model.debounce.300ms="search" placeholder="Search..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
        </div>
        <div class="w-48">
            <select wire:model="filterStatus" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        <div class="w-48">
            <select wire:model="filterDepartment" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
            <li class="mr-2">
                <button wire:click="switchTab('job-postings')" class="inline-block p-4 {{ $activeTab === 'job-postings' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Job Postings
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="switchTab('applicants')" class="inline-block p-4 {{ $activeTab === 'applicants' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Applicants
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="switchTab('interviews')" class="inline-block p-4 {{ $activeTab === 'interviews' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Interviews
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="switchTab('onboarding')" class="inline-block p-4 {{ $activeTab === 'onboarding' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Onboarding
                </button>
            </li>
        </ul>
    </div>

    <!-- Job Postings Tab -->
    <div class="{{ $activeTab === 'job-postings' ? '' : 'hidden' }}">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Job Postings</h2>
            <button wire:click="openJobModal" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Post New Job
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($jobPostings as $job)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->job_title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->department }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $job->job_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $job->status === 'open' ? 'bg-green-100 text-green-800' : 
                                   ($job->status === 'closed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="editJobPosting({{ $job->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button wire:click="deleteJobPosting({{ $job->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $jobPostings->links() }}
        </div>
    </div>

    <!-- Applicants Tab -->
    <div class="{{ $activeTab === 'applicants' ? '' : 'hidden' }}">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Applicants</h2>
            <button wire:click="openApplicantModal" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Add New Applicant
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Applied</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($applicants as $applicant)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $applicant->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $applicant->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $applicant->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $applicant->jobPosting->job_title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $applicant->status === 'new' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($applicant->status === 'reviewing' ? 'bg-red-100 text-red-800' : 
                                   ($applicant->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800')) }}">
                                {{ ucfirst($applicant->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="editApplicant({{ $applicant->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button wire:click="deleteApplicant({{ $applicant->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $applicants->links() }}
        </div>
    </div>

    <!-- Interviews Tab -->
    <div class="{{ $activeTab === 'interviews' ? '' : 'hidden' }}">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Interviews</h2>
            <button wire:click="openInterviewModal" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Schedule Interview
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interviewer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($interviews as $interview)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $interview->applicant->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $interview->interview_date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($interview->interview_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $interview->interviewer }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $interview->status === 'scheduled' ? 'bg-red-100 text-red-800' : 
                                   ($interview->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($interview->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="editInterview({{ $interview->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button wire:click="deleteInterview({{ $interview->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $interviews->links() }}
        </div>
    </div>

    <!-- Onboarding Tab -->
    <div class="{{ $activeTab === 'onboarding' ? '' : 'hidden' }}">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Employee Onboarding</h2>
                <p class="text-gray-600 mt-1">Manage new employee onboarding process</p>
            </div>
            <button wire:click="openOnboardingModal" 
                class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-600 transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Start New Onboarding
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" wire:model.debounce.300ms="search" 
                        placeholder="Search by employee name or position..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="w-[200px]">
                    <select wire:model="filterStatus" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Onboarding List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($onboardings as $onboarding)
                        <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 font-medium">
                                                {{ substr($onboarding->applicant->name, 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $onboarding->applicant->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $onboarding->applicant->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $onboarding->jobPosting->job_title }}</div>
                                <div class="text-sm text-gray-500">{{ $onboarding->jobPosting->department }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $onboarding->start_date ? $onboarding->start_date->format('M d, Y') : 'Not set' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $onboarding->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($onboarding->status === 'in_progress' ? 'bg-red-100 text-red-800' : 
                                   ($onboarding->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $onboarding->status)) }}
                            </span>
                        </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $onboarding->status === 'completed' ? '100' : ($onboarding->status === 'in_progress' ? '50' : '0') }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="editOnboarding({{ $onboarding->id }})" 
                                    class="text-red-600 hover:text-red-900 mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="deleteOnboarding({{ $onboarding->id }})" 
                                    class="text-red-600 hover:text-red-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
            <div class="px-6 py-4 border-t border-gray-200">
            {{ $onboardings->links() }}
            </div>
        </div>
    </div>

    <!-- Job Posting Modal -->
    <div class="{{ $showJobModal ? 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full' : 'hidden' }}">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ $editingJobId ? 'Edit Job Posting' : 'Post New Job' }}
                </h3>
                <form wire:submit.prevent="{{ $editingJobId ? 'updateJobPosting' : 'createJobPosting' }}">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jobTitle">
                            Job Title
                        </label>
                        <input wire:model="jobTitle" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="jobTitle">
                        @error('jobTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="department">
                            Department
                        </label>
                        <select wire:model="department" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="department">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                        @error('department') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                            Location
                        </label>
                        <input wire:model="location" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="location">
                        @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jobType">
                            Job Type
                        </label>
                        <select wire:model="jobType" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="jobType">
                            <option value="">Select Type</option>
                            <option value="full-time">Full Time</option>
                            <option value="part-time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                        </select>
                        @error('jobType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                            Description
                        </label>
                        <textarea wire:model="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" rows="3"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="requirements">
                            Requirements
                        </label>
                        <textarea wire:model="requirements" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="requirements" rows="3"></textarea>
                        @error('requirements') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="salary">
                            Salary
                        </label>
                        <input wire:model="salary" type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="salary">
                        @error('salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jobStatus">
                            Status
                        </label>
                        <select wire:model="jobStatus" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="jobStatus">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="draft">Draft</option>
                        </select>
                        @error('jobStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ $editingJobId ? 'Update' : 'Create' }}
                        </button>
                        <button type="button" wire:click="closeJobModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Applicant Modal -->
    <div class="{{ $showApplicantModal ? 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full' : 'hidden' }}">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ $editingApplicantId ? 'Edit Applicant' : 'Add New Applicant' }}
                </h3>
                <form wire:submit.prevent="{{ $editingApplicantId ? 'updateApplicant' : 'createApplicant' }}">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                            Name
                        </label>
                        <input wire:model="name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input wire:model="email" type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                            Phone
                        </label>
                        <input wire:model="phone" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="resume">
                            Resume
                        </label>
                        <input wire:model="resume" type="file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="resume">
                        @error('resume') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="coverLetter">
                            Cover Letter
                        </label>
                        <textarea wire:model="coverLetter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="coverLetter" rows="3"></textarea>
                        @error('coverLetter') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jobPostingId">
                            Job Applied For
                        </label>
                        <select wire:model="jobPostingId" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="jobPostingId">
                            <option value="">Select Job</option>
                            @foreach($jobPostings as $job)
                                <option value="{{ $job->id }}">{{ $job->job_title }}</option>
                            @endforeach
                        </select>
                        @error('jobPostingId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ $editingApplicantId ? 'Update' : 'Create' }}
                        </button>
                        <button type="button" wire:click="closeApplicantModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Interview Modal -->
    <div class="{{ $showInterviewModal ? 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full' : 'hidden' }}">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ $editingInterviewId ? 'Edit Interview' : 'Schedule Interview' }}
                </h3>
                <form wire:submit.prevent="{{ $editingInterviewId ? 'updateInterview' : 'createInterview' }}">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="applicantId">
                            Applicant
                        </label>
                        <select wire:model="applicantId" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="applicantId">
                            <option value="">Select Applicant</option>
                            @foreach($applicants as $applicant)
                                <option value="{{ $applicant->id }}">{{ $applicant->name }}</option>
                            @endforeach
                        </select>
                        @error('applicantId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="interviewDate">
                            Interview Date
                        </label>
                        <input wire:model="interviewDate" type="datetime-local" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="interviewDate">
                        @error('interviewDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="interviewType">
                            Interview Type
                        </label>
                        <select wire:model="interviewType" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="interviewType">
                            <option value="">Select Type</option>
                            <option value="phone">Phone</option>
                            <option value="video">Video</option>
                            <option value="in-person">In Person</option>
                        </select>
                        @error('interviewType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="interviewer">
                            Interviewer
                        </label>
                        <input wire:model="interviewer" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="interviewer">
                        @error('interviewer') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                            Notes
                        </label>
                        <textarea wire:model="notes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="notes" rows="3"></textarea>
                        @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="interviewStatus">
                            Status
                        </label>
                        <select wire:model="interviewStatus" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="interviewStatus">
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        @error('interviewStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ $editingInterviewId ? 'Update' : 'Schedule' }}
                        </button>
                        <button type="button" wire:click="closeInterviewModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Onboarding Modal -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 transition-opacity duration-300" 
     x-show="$wire.showOnboardingModal" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">

    <div class="relative mx-auto my-8 p-0 w-full max-w-4xl">
        <!-- Modal Container -->
        <div class="relative bg-white rounded-xl shadow-2xl overflow-hidden transition-all transform"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-white">Employee Onboarding</h3>
                    <button wire:click="closeOnboardingModal" 
                            class="text-gray-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

                <!-- Progress Steps -->
                <div class="mt-2 mb-8">
                    <ol class="flex items-center w-full p-3 space-x-2 text-sm font-medium text-center text-gray-500 bg-white border border-gray-200 rounded-lg shadow-xs dark:text-gray-400 sm:text-base dark:bg-gray-800 dark:border-gray-700 sm:p-4 sm:space-x-4 rtl:space-x-reverse">
                        @foreach(['Personal Info', 'Documents', 'Payroll & Assignment', 'Control Numbers'] as $index => $step)
                        <li class="flex items-center text-red-600 dark:text-red-500 ">
                            <span class="flex items-center justify-center w-5 h-5 me-2 text-xs border border-red-600 rounded-full shrink-0 dark:border-red-500
                            {{ $currentStep >= $index ? 'text-red-600' : 'text-gray-600' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="hidden sm:inline-flex sm:ms-2
                            {{ $currentStep >= $index ? 'text-red-600' : 'text-gray-600' }}">
                                {{ $step }} 
                            </span>
                            <svg class="w-3 h-3 ms-2 sm:ms-4 rtl:rotate-180 {{ $currentStep >= $index ? 'text-red-600' : 'text-gray-600' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 12 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 9 4-4-4-4M1 9l4-4-4-4"/>
                            </svg>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <!-- Form Content -->
            <form wire:submit.prevent="createOnboarding" class="max-h-[calc(100vh-200px)] overflow-y-auto p-6">
                <!-- Step 1: Personal Information -->
                <div class="{{ $currentStep === 0 ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="firstName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('firstName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" wire:model="middleName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('middleName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="lastName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('lastName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="dob" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('dob') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gender <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="gender" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Marital Status <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="maritalStatus" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                            @error('maritalStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nationality <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="nationality" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Nationality</option>
                                <option value="Tanzanian">Tanzanian</option>
                                <option value="Kenyan">Kenyan</option>
                                <option value="Ugandan">Ugandan</option>
                                <option value="Rwandan">Rwandan</option>
                                <option value="Burundian">Burundian</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('nationality') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" wire:model="phone" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIDA Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nida" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('nida') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">TIN Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="tin" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('tin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Address Information -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Physical Address <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="physicalAddress" placeholder="Street Address" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('physicalAddress') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">City/Town <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="city" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Region <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="region" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Region</option>
                                <option value="Dar es Salaam">Dar es Salaam</option>
                                <option value="Arusha">Arusha</option>
                                <option value="Mwanza">Mwanza</option>
                                <option value="Dodoma">Dodoma</option>
                                <option value="Mbeya">Mbeya</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('region') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Emergency Contact Information -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Emergency Contact Information</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Emergency Contact Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="emergencyContactName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('emergencyContactName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Relationship <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="emergencyContactRelationship" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Relationship</option>
                                <option value="spouse">Spouse</option>
                                <option value="parent">Parent</option>
                                <option value="sibling">Sibling</option>
                                <option value="relative">Relative</option>
                                <option value="friend">Friend</option>
                                <option value="other">Other</option>
                            </select>
                            @error('emergencyContactRelationship') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Emergency Contact Phone <span class="text-red-500">*</span></label>
                            <input type="tel" wire:model="emergencyContactPhone" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('emergencyContactPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Emergency Contact Email</label>
                            <input type="email" wire:model="emergencyContactEmail" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('emergencyContactEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        </div>
                        </div>

                <!-- Step 2: Documents -->
                <div class="{{ $currentStep === 1 ? '' : 'hidden' }}">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">Required Documents</h4>
                    <p class="text-sm text-gray-600 mb-6">Please upload the following documents in PDF or image format (max 5MB each).</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- CV/Resume -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CV/Resume</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                            <span>Upload a file</span>
                                            <input type="file" wire:model="cv" class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                        </div>
                                    <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                    </div>
                </div>
                            @error('cv') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="cv" class="text-xs text-red-600">Uploading...</div>
                            @if($cv)
                                <div class="flex items-center text-sm text-green-600 mt-2">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    File uploaded successfully
                                </div>
                            @endif
                        </div>

                        <!-- National ID -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">National ID</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                            <span>Upload a file</span>
                                            <input type="file" wire:model="nationalId" class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                        </div>
                                    <p class="text-xs text-gray-500">PDF, JPG, PNG up to 2MB</p>
                        </div>
                    </div>
                            @error('nationalId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="nationalId" class="text-xs text-red-600">Uploading...</div>
                            @if($nationalId)
                                <div class="flex items-center text-sm text-green-600 mt-2">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    File uploaded successfully
                </div>
                            @endif
                        </div>

                        <!-- Passport Photo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Passport Photo</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                            <span>Upload a file</span>
                                            <input type="file" wire:model="passportPhoto" class="sr-only">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">JPG, PNG up to 2MB</p>
                                </div>
                            </div>
                            @error('passportPhoto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="passportPhoto" class="text-xs text-red-600">Uploading...</div>
                            @if($passportPhoto)
                                <div class="flex items-center text-sm text-green-600 mt-2">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    File uploaded successfully
                                </div>
                            @endif
                        </div>

                        <!-- Employment Contract -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Employment Contract <span class="text-red-500">*</span></label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="employmentContract" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                            <span>Upload a file</span>
                                            <input id="employmentContract" wire:model="employmentContract" type="file" class="sr-only" accept=".pdf">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF up to 2MB</p>
                                </div>
                            </div>
                            @error('employmentContract') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                        </div>

                <!-- Step 3: Payroll & Assignment -->
                <div class="{{ $currentStep === 2 ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Department & Role Assignment -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Department & Role Assignment</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="department_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="role_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Branch <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="branch_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reporting Manager <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="reporting_manager_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Manager</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                            @error('reporting_manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employment Type <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="employment_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Type</option>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="internship">Internship</option>
                            </select>
                            @error('employment_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="start_date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Payroll Information -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payroll Information</h3>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Basic Salary <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">TZS</span>
                        </div>
                                <input type="number" wire:model="basicSalary" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 pl-12" placeholder="0.00">
                    </div>
                            @error('basicSalary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Frequency <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="paymentFrequency" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Frequency</option>
                                <option value="monthly">Monthly</option>
                                <option value="biweekly">Bi-weekly</option>
                                <option value="weekly">Weekly</option>
                            </select>
                            @error('paymentFrequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Social Security -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Social Security</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NSSF Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nssfNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="NSSF Number">
                            @error('nssfNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NSSF Contribution Rate <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="nssfRate" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Rate</option>
                                <option value="5">5% (Employee)</option>
                                <option value="10">10% (Employee + Employer)</option>
                            </select>
                            @error('nssfRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Health Insurance -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Health Insurance</h4>
                </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NHIF Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nhifNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="NHIF Number">
                            @error('nhifNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NHIF Contribution Rate <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="nhifRate" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Rate</option>
                                <option value="3">3% (Employee)</option>
                                <option value="6">6% (Employee + Employer)</option>
                            </select>
                            @error('nhifRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Additional Insurance -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Additional Insurance</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Workers Compensation Insurance <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="workersCompensation" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Option</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                            @error('workersCompensation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Life Insurance <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="lifeInsurance" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Option</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                            @error('lifeInsurance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tax Information -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-medium text-gray-800 mb-3">Tax Information</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">TIN Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="tinNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            @error('tinNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tax Category <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="taxCategory" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Category</option>
                                <option value="A">Category A</option>
                                <option value="B">Category B</option>
                                <option value="C">Category C</option>
                            </select>
                            @error('taxCategory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">PAYE Rate <span class="text-red-500">*</span></label>
                            <select type="text" wire:model="payeRate" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Select Rate</option>
                                <option value="0">0% (Up to 270,000 TZS)</option>
                                <option value="8">8% (270,001 - 520,000 TZS)</option>
                                <option value="20">20% (520,001 - 760,000 TZS)</option>
                                <option value="25">25% (760,001 - 1,000,000 TZS)</option>
                                <option value="30">30% (Above 1,000,000 TZS)</option>
                            </select>
                            @error('payeRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 4: Control Numbers -->
                <div class="{{ $currentStep === 3 ? '' : 'hidden' }}" x-transition>
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow" id="controlNumbersReceipt">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="text-center flex-1">
                                        <h3 class="text-lg font-semibold">Control Numbers Receipt</h3>
                                        <p class="text-sm text-gray-600">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
                                    </div>
                                    <button type="button" onclick="printReceipt()" class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </button>
                        </div>

                                <div class="mb-4">
                                    <p class="font-semibold">Employee Information:</p>
                                    <p>Name: {{ $fullName ?? (isset($first_name) ? trim("$first_name $middle_name $last_name") : '') }}</p>
                                    <p>Phone: {{ $phone ?? $phone_number }}</p>
                                    @if($email)
                                        <p>Email: {{ $email }}</p>
                                    @endif
                        </div>

                                <div class="border-t border-b py-4 my-4">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Service</th>
                                                <th class="text-left">Control Number</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($generatedControlNumbers as $control)
                                                <tr>
                                                    <td>{{ $control['service_code'] }}</td>
                                                    <td class="font-mono">{{ $control['control_number'] }}</td>
                                                    <td class="text-right">{{ number_format($control['amount'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-sm text-gray-600 mt-4">
                                    <p>Please keep these control numbers safe for future reference.</p>
                                    <p>You can use these numbers to make payments at any of our branches.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 pt-5 border-t border-gray-200 flex items-center justify-between">
                    <div>
                        <button type="button" wire:click="previousStep" 
                                class="{{ $currentStep === 0 ? 'invisible' : '' }} inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        Previous
                    </button>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" wire:click="closeOnboardingModal" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel
                        </button>
                        
                        @if($currentStep < 3)
                            <button type="button" wire:click="nextStep" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Next
                                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @else
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Complete Onboarding
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="fixed bottom-0 right-0 m-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- File Upload Styles -->
    <style nonce="{{ csp_nonce() }}">
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 60px;
        }
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-upload-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            border: 1px solid #e2e8f0;
        }
        .file-preview-name {
            font-size: 0.875rem;
            color: #4a5568;
        }
        .file-preview-remove {
            color: #e53e3e;
            cursor: pointer;
        }
    </style>
</div> 