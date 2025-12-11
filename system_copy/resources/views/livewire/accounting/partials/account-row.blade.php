@php
    // Calculate indentation based on level (increasing for deeper levels)
    $indentPixels = $level * 32; // 32px per level (equivalent to 8 in Tailwind spacing)
    $hasChildren = isset($account->children) && count($account->children) > 0;
    $isExpanded = in_array($account->account_number, $expandedAccounts);
    
    // Use display_type if available, otherwise determine from account name/category
    $accountType = $account->display_type ?? $account->type ?? '';
    
    // Determine type from account name or category if type is not set properly
    if (!in_array($accountType, ['ASSET', 'LIABILITY', 'EQUITY', 'INCOME', 'EXPENSE'])) {
        if (str_contains(strtoupper($account->account_name), 'ASSET') || $account->major_category_code == '1000') {
            $accountType = 'ASSET';
        } elseif (str_contains(strtoupper($account->account_name), 'LIABILIT') || $account->major_category_code == '2000') {
            $accountType = 'LIABILITY';
        } elseif (str_contains(strtoupper($account->account_name), 'EQUITY') || str_contains(strtoupper($account->account_name), 'CAPITAL') || $account->major_category_code == '3000') {
            $accountType = 'EQUITY';
        } elseif (str_contains(strtoupper($account->account_name), 'REVENUE') || str_contains(strtoupper($account->account_name), 'INCOME') || $account->major_category_code == '4000') {
            $accountType = 'INCOME';
        } elseif (str_contains(strtoupper($account->account_name), 'EXPENSE') || $account->major_category_code == '5000') {
            $accountType = 'EXPENSE';
        }
    }
    
    // Professional color scheme - only blue-900 and red for accents
    $typeIcon = match($accountType) {
        'ASSET' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'LIABILITY' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>',
        'EQUITY' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
        'INCOME' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
        'EXPENSE' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>',
        default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>'
    };
    
    // Level-based styling for better visual hierarchy
    $levelBadge = match($account->account_level) {
        '1' => 'bg-blue-900 text-white',
        '2' => 'bg-blue-700 text-white',
        '3' => 'bg-gray-200 text-gray-700',
        '4' => 'bg-gray-100 text-gray-600',
        default => 'bg-gray-50 text-gray-500'
    };
    
    // Row background based on level for better visual separation
    $rowBackground = match($account->account_level) {
        '1' => '',  // No background for level 1
        '2' => 'bg-gray-50',
        '3' => 'bg-gray-50',
        '4' => 'bg-gray-100',
        default => ''
    };
    
    // Font weight based on level
    $fontWeight = match($account->account_level) {
        '1' => 'font-bold text-gray-900',
        '2' => 'font-semibold text-gray-800',
        '3' => 'font-medium text-gray-700',
        '4' => 'font-normal text-gray-600',
        default => 'font-normal text-gray-600'
    };
    
    // Determine if balance is negative for styling
    $balance = floatval($account->current_balance ?? $account->balance ?? 0);
    $isNegative = $balance < 0;
@endphp

<tr class="{{ $rowBackground }} hover:bg-blue-50 transition-colors {{ $level > 0 ? 'border-l-2 border-gray-300' : '' }}">
    <td class="py-3 whitespace-nowrap">
        <div class="flex items-center" style="padding-left: {{ 24 + $indentPixels }}px;">
            @if($level > 0)
                <!-- Hierarchy connector lines -->
                <div class="relative">
                    <div class="absolute -left-4 top-0 bottom-0 w-px bg-gray-300"></div>
                    <div class="absolute -left-4 top-1/2 w-3 h-px bg-gray-300"></div>
                </div>
            @endif
            
            @if($hasChildren)
                <button wire:click="toggleAccount('{{ $account->account_number }}')" 
                    class="mr-2 p-1 rounded text-gray-500 hover:text-blue-900 hover:bg-gray-100 focus:outline-none transition-all">
                    @if($isExpanded)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </button>
            @else
                @if($level > 0)
                    <span class="inline-block w-7 mr-2"></span>
                @else
                    <span class="inline-block w-7 mr-2"></span>
                @endif
            @endif
            
            <div class="flex items-center">
                <!-- Level indicator -->
                @if($level > 0)
                    <span class="mr-2 text-gray-400">
                        @for($i = 1; $i < $level; $i++)
                            <span class="inline-block w-1 h-1 bg-gray-400 rounded-full mr-1"></span>
                        @endfor
                    </span>
                @endif
                
                <svg class="w-4 h-4 mr-2 {{ $accountType == 'LIABILITY' || $accountType == 'EXPENSE' ? 'text-red-600' : 'text-blue-900' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $typeIcon !!}
                </svg>
                <div>
                    <div class="text-sm {{ $fontWeight }}">
                        {{ $account->account_name }}
                    </div>
                    @if($account->notes)
                        <div class="text-xs text-gray-500">{{ Str::limit($account->notes, 50) }}</div>
                    @endif
                </div>
            </div>
        </div>
    </td>
    <td class="px-6 py-3 whitespace-nowrap">
        <div class="text-sm text-gray-900 font-mono">{{ $account->account_number }}</div>
        @if($account->major_category_code)
            <div class="text-xs text-gray-500 font-mono">
                {{ $account->major_category_code }}-{{ $account->category_code }}-{{ $account->sub_category_code }}
            </div>
        @endif
    </td>
    <td class="px-6 py-3 whitespace-nowrap">
        <span class="text-sm font-medium text-gray-700">
            {{ $accountTypes[$accountType] ?? $accountType }}
        </span>
    </td>
    <td class="px-6 py-3 whitespace-nowrap">
        <div>
            <span class="px-2 py-1 text-xs rounded {{ $levelBadge }}">
                L{{ $account->account_level }}
            </span>
            @if($account->account_use)
                <div class="text-xs text-gray-500 mt-1">{{ ucfirst($account->account_use) }}</div>
            @endif
        </div>
    </td>
    <td class="px-6 py-3 whitespace-nowrap text-right">
        <span class="text-sm text-gray-900 font-mono">
            {{ $account->debit ? number_format(floatval($account->debit), 2) : '-' }}
        </span>
    </td>
    <td class="px-6 py-3 whitespace-nowrap text-right">
        <span class="text-sm text-gray-900 font-mono">
            {{ $account->credit ? number_format(floatval($account->credit), 2) : '-' }}
        </span>
    </td>
    <td class="px-6 py-3 whitespace-nowrap text-right">
        <div class="space-y-1">
            <span class="text-sm {{ $fontWeight }} {{ $isNegative ? 'text-red-600' : '' }} font-mono block">
                {{ number_format($balance, 2) }}
            </span>
            @if($hasChildren && isset($account->total_balance) && $account->total_balance != $balance)
                <div class="flex items-center justify-end space-x-1">
                    <span class="text-xs text-gray-400">Total:</span>
                    <span class="text-xs font-semibold text-gray-600 font-mono">
                        {{ number_format($account->total_balance, 2) }}
                    </span>
                </div>
            @endif
        </div>
    </td>
    <td class="px-6 py-3 whitespace-nowrap text-center">
        <div class="flex items-center justify-center space-x-1">
            <button wire:click="viewLedger('{{ $account->account_number }}')" 
                class="p-1 text-blue-900 hover:text-blue-700 hover:bg-blue-50 rounded transition-all focus:outline-none" 
                title="View Ledger">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </button>
            <button wire:click="viewAccountDetails('{{ $account->account_number }}')" 
                class="p-1 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded transition-all focus:outline-none" 
                title="Account Details">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
            <button wire:click="openCreateModal('{{ $account->account_number }}')" 
                class="p-1 text-blue-900 hover:text-blue-700 hover:bg-blue-50 rounded transition-all focus:outline-none" 
                title="Add Sub-Account">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button wire:click="openEditModal('{{ $account->account_number }}')" 
                class="p-1 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded transition-all focus:outline-none" 
                title="Edit Account">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            @if($account->status == 'ACTIVE')
                <button wire:click="confirmBlockAccount('{{ $account->account_number }}')" 
                    class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-all focus:outline-none" 
                    title="Block Account">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </button>
            @else
                <button wire:click="unblockAccount('{{ $account->account_number }}')" 
                    class="p-1 text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition-all focus:outline-none" 
                    title="Unblock Account">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
            @endif
        </div>
    </td>
</tr>

@if($hasChildren && $isExpanded)
    @foreach($account->children as $child)
        @include('livewire.accounting.partials.account-row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif