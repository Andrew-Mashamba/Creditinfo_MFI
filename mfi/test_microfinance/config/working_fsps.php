<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production-Ready FSPs Configuration
    |--------------------------------------------------------------------------
    |
    | These FSPs have been tested and confirmed working in NBC UAT environment
    | Last Updated: 2025-09-03
    |
    */

    'production_ready' => true,
    
    'banks' => [
        'CRDB' => [
            'name' => 'CRDB BANK PLC',
            'code' => 'CORUTZTZ',
            'fsp_id' => '003',
            'active' => true,
            'average_response_time' => 860, // milliseconds
            'test_account' => '12334567789',
            'priority' => 1
        ],
        'NMB' => [
            'name' => 'NMB Bank',
            'code' => 'NMIBTZT0', 
            'fsp_id' => '016',
            'active' => true,
            'average_response_time' => 743, // milliseconds
            'test_account' => '20110033445',
            'priority' => 2
        ],
        'NBC' => [
            'name' => 'NBC (Internal)',
            'code' => 'NLCBTZTX',
            'fsp_id' => '015',
            'active' => true,
            'average_response_time' => 25425, // milliseconds - needs optimization
            'test_account' => '011201318462',
            'priority' => 3,
            'is_internal' => true,
            'needs_optimization' => true
        ]
    ],
    
    'mobile_wallets' => [
        'MPESA' => [
            'name' => 'Vodacom M-Pesa',
            'code' => 'VMCASHIN',
            'fsp_id' => '503',
            'active' => true,
            'average_response_time' => 1870, // milliseconds
            'test_phone' => '0748045601',
            'priority' => 1
        ]
    ],
    
    'pending_testing' => [
        'banks' => [
            'STANBIC', 'ABSA', 'KCB', 'DTB', 'EQUITY', 'EXIM', 
            'GTB', 'HABIB', 'I_AND_M', 'ICB', 'LETSHEGO', 'MUCOBA',
            'MWANGA', 'NCBA', 'SELCOM', 'TCB', 'UBA', 'UCHUMI',
            'BOA', 'CITIBANK', 'DCB', 'ECO', 'AKIBA', 'AZANIA',
            'BARODA', 'CHINA'
        ],
        'wallets' => [
            'TIGOPESA', 'AIRTEL', 'HALOPESA', 'AZAMPESA', 'TPESA', 'ZANTEL'
        ]
    ],
    
    'known_issues' => [
        'STANBIC' => 'Failed to retrieve beneficiary information',
        'TIGOPESA' => 'BOT Service timeout (30s)',
        'NBC' => 'Slow response time (25s) - needs optimization'
    ]
];