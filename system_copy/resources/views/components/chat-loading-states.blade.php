@props(['type' => 'thinking', 'message' => null])

@php
$loadingMessages = [
    'thinking' => [
        'AI is thinking...',
        'Processing your request...',
        'Analyzing the data...',
        'Formulating response...',
        'Almost there...'
    ],
    'database' => [
        'Querying database...',
        'Fetching records...',
        'Processing results...',
        'Analyzing data patterns...',
        'Preparing insights...'
    ],
    'file' => [
        'Reading file contents...',
        'Analyzing document...',
        'Extracting information...',
        'Processing file data...',
        'Preparing analysis...'
    ],
    'report' => [
        'Generating report...',
        'Compiling data...',
        'Creating visualizations...',
        'Formatting output...',
        'Finalizing report...'
    ]
];

$currentMessages = $loadingMessages[$type] ?? $loadingMessages['thinking'];
@endphp

<div class="flex items-start space-x-3" 
     x-data="{ 
         messageIndex: 0,
         messages: @js($currentMessages),
         currentMessage: @js($currentMessages[0])
     }"
     x-init="
         setInterval(() => {
             messageIndex = (messageIndex + 1) % messages.length;
             currentMessage = messages[messageIndex];
         }, 2000)
     ">
    
    <!-- AI Avatar with Animated Glow -->
    <div class="flex-shrink-0 relative">
        <div class="absolute inset-0 bg-blue-400 rounded-xl blur-xl opacity-50 animate-pulse"></div>
        <div class="relative w-10 h-10 bg-blue-800 rounded-xl flex items-center justify-center shadow-lg">
            <!-- Spinning Brain Icon -->
            <svg class="w-6 h-6 text-white animate-spin" style="animation-duration: 3s" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Loading Content Area -->
    <div class="flex-1 max-w-3xl">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Loading Bar at Top -->
            <div class="h-1 bg-gray-100 relative overflow-hidden">
                <div class="absolute inset-0 bg-blue-500 animate-shimmer"></div>
            </div>
            
            <!-- Main Loading Content -->
            <div class="p-4">
                <!-- Type-specific Icon and Message -->
                <div class="flex items-start space-x-3">
                    <!-- Dynamic Icon based on type -->
                    <div class="flex-shrink-0">
                        @switch($type)
                            @case('database')
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                    </svg>
                                </div>
                                @break
                            @case('file')
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                @break
                            @case('report')
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                        @endswitch
                    </div>
                    
                    <!-- Loading Text and Animations -->
                    <div class="flex-1">
                        <!-- Rotating Messages -->
                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-700" x-text="currentMessage"></span>
                        </div>
                        
                        <!-- Animated Dots -->
                        <div class="flex items-center space-x-3">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-blue-900 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                            
                            <!-- Progress Indicator -->
                            <div class="flex-1 max-w-xs">
                                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full animate-progress"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Context Message -->
                        @if($message)
                        <div class="mt-2">
                            <span class="text-xs text-gray-500">{{ $message }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Skeleton Content Preview -->
                <div class="mt-3 space-y-2 animate-pulse">
                    <div class="h-2 bg-gray-100 rounded w-3/4"></div>
                    <div class="h-2 bg-gray-100 rounded w-1/2"></div>
                    <div class="h-2 bg-gray-100 rounded w-5/6"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style nonce="{{ csp_nonce() }}">
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
}

.animate-progress {
    animation: progress 3s ease-in-out infinite;
}
</style>