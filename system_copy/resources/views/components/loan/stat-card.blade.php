@props(['color' => 'blue', 'label' => '', 'value' => 0, 'icon' => 'chart-bar'])

@php
    $colorClasses = [
        'blue' => [
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-600',
            'icon' => 'text-blue-600'
        ],
        'yellow' => [
            'bg' => 'bg-yellow-100',
            'text' => 'text-yellow-600',
            'icon' => 'text-yellow-600'
        ],
        'green' => [
            'bg' => 'bg-green-100',
            'text' => 'text-green-600',
            'icon' => 'text-green-600'
        ],
        'red' => [
            'bg' => 'bg-red-100',
            'text' => 'text-red-600',
            'icon' => 'text-red-600'
        ],
        'purple' => [
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-600',
            'icon' => 'text-purple-600'
        ],
        'indigo' => [
            'bg' => 'bg-indigo-100',
            'text' => 'text-indigo-600',
            'icon' => 'text-indigo-600'
        ]
    ];

    $iconClasses = $colorClasses[$color] ?? $colorClasses['blue'];
    
    $icons = [
        'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'x-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>',
        'currency-dollar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>',
        'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
        'exclamation-triangle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>'
    ];

    $iconPath = $icons[$icon] ?? $icons['chart-bar'];
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center">
        <div class="p-3 {{ $iconClasses['bg'] }} rounded-xl">
            <svg class="w-6 h-6 {{ $iconClasses['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                {!! $iconPath !!}
            </svg>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($value) }}</p>
        </div>
    </div>
</div> 