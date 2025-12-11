<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Financial Service Providers Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all active FSPs from NBC Payment Gateway
    | Retrieved from: /domestix/info/api/v2/financial-service-providers
    | Last Updated: 2025-09-03
    |
    */

    'banks' => [
        'ABSA' => [
            'name' => 'ABSA BANK TANZANIA LIMITED',
            'code' => 'BARCTZT0',
            'fsp_id' => '020',
            'active' => true,
            'tips_enabled' => true
        ],
        'AKIBA' => [
            'name' => 'AKIBA COMMERCIAL BANK PLC',
            'code' => 'AKCOTZTZ',
            'fsp_id' => '012',
            'active' => true,
            'tips_enabled' => true
        ],
        'AZANIA' => [
            'name' => 'AZANIA BANK',
            'code' => 'AZANTZTZ',
            'fsp_id' => '031',
            'active' => true,
            'tips_enabled' => true
        ],
        'BARODA' => [
            'name' => 'BARODA',
            'code' => 'BARBTZTZ',
            'fsp_id' => '029',
            'active' => true,
            'tips_enabled' => true
        ],
        'BOA' => [
            'name' => 'BANK OF AFRICA TANZANIA LIMITED',
            'code' => 'EUAFTZTZ',
            'fsp_id' => '009',
            'active' => true,
            'tips_enabled' => true
        ],
        'CHINA' => [
            'name' => 'CHINA DASHENG BANK',
            'code' => 'CDSHTZTZ',
            'fsp_id' => '065',
            'active' => true,
            'tips_enabled' => true
        ],
        'CITIBANK' => [
            'name' => 'CITI BANK',
            'code' => 'CITITZTZ',
            'fsp_id' => '008',
            'active' => true,
            'tips_enabled' => true
        ],
        'CRDB' => [
            'name' => 'CRDB BANK PLC',
            'code' => 'CORUTZTZ',
            'fsp_id' => '003',
            'active' => true,
            'tips_enabled' => true,
            'tested' => true,
            'working' => true
        ],
        'DCB' => [
            'name' => 'DCB COMMERCIAL BANK',
            'code' => 'DASUTZTZ',
            'fsp_id' => '024',
            'active' => true,
            'tips_enabled' => true
        ],
        'DTB' => [
            'name' => 'DIAMOND TRUST BANK',
            'code' => 'DTKETZTZ',
            'fsp_id' => '011',
            'active' => true,
            'tips_enabled' => true
        ],
        'ECO' => [
            'name' => 'ECO BANK',
            'code' => 'ECOCTZTZ',
            'fsp_id' => '040',
            'active' => true,
            'tips_enabled' => true
        ],
        'EQUITY' => [
            'name' => 'EQUITY BANK (T) LTD',
            'code' => 'EQBLTZTZ',
            'fsp_id' => '047',
            'active' => true,
            'tips_enabled' => true
        ],
        'EXIM' => [
            'name' => 'EXIM',
            'code' => 'EXTNTZT0',
            'fsp_id' => '013',
            'active' => true,
            'tips_enabled' => true
        ],
        'GTB' => [
            'name' => 'GUARANTY TRUST BANK TANZANIA LTD',
            'code' => 'GTBITZT0',
            'fsp_id' => '061',
            'active' => true,
            'tips_enabled' => true
        ],
        'HABIB' => [
            'name' => 'HABIB AFRICAN BANK',
            'code' => 'HABLTZTZ',
            'fsp_id' => '018',
            'active' => true,
            'tips_enabled' => true
        ],
        'I_AND_M' => [
            'name' => 'I AND M BANK (T) LTD',
            'code' => 'IMBLTZTZ',
            'fsp_id' => '021',
            'active' => true,
            'tips_enabled' => true
        ],
        'ICB' => [
            'name' => 'INTERNATIONAL COMMERCIAL BANK (TANZANIA) LIMITED',
            'code' => 'BKMYTZTZ',
            'fsp_id' => '019',
            'active' => true,
            'tips_enabled' => true
        ],
        'KCB' => [
            'name' => 'KCB BANK TANZANIA',
            'code' => 'KCBLTZTZ',
            'fsp_id' => '017',
            'active' => true,
            'tips_enabled' => true
        ],
        'LETSHEGO' => [
            'name' => 'LETSHEGO BANK TANZANIA LTD CLEARING',
            'code' => 'ADVBTZTZ',
            'fsp_id' => '044',
            'active' => true,
            'tips_enabled' => true
        ],
        'MUCOBA' => [
            'name' => 'MUCOBA BANK PLC',
            'code' => 'MUOBTZTZ',
            'fsp_id' => '064',
            'active' => true,
            'tips_enabled' => true
        ],
        'MWANGA' => [
            'name' => 'MWANGA HAKIKA MICROFINANCE BANK',
            'code' => 'MWCBTZTZ',
            'fsp_id' => '042',
            'active' => true,
            'tips_enabled' => true
        ],
        'NBC' => [
            'name' => 'NBC',
            'code' => 'NLCBTZTX',
            'fsp_id' => '015',
            'active' => true,
            'tips_enabled' => true,
            'is_self' => true
        ],
        'NCBA' => [
            'name' => 'NCBA BANK TANZANIA LIMITED',
            'code' => 'CBAFTZTZ',
            'fsp_id' => '023',
            'active' => true,
            'tips_enabled' => true
        ],
        'NMB' => [
            'name' => 'NMB Bank',
            'code' => 'NMIBTZT0',
            'fsp_id' => '016',
            'active' => true,
            'tips_enabled' => true,
            'tested' => true,
            'working' => false // Not onboarded for TIPS in UAT
        ],
        'SELCOM' => [
            'name' => 'SELCOM',
            'code' => 'ACTZTZTZ',
            'fsp_id' => '035',
            'active' => true,
            'tips_enabled' => true
        ],
        'STANBIC' => [
            'name' => 'STANBIC BANK',
            'code' => 'SBICTZTX',
            'fsp_id' => '006',
            'active' => true,
            'tips_enabled' => true
        ],
        'TCB' => [
            'name' => 'TANZANIA COMMERCIAL BANK PLC',
            'code' => 'TAPBTZTZ',
            'fsp_id' => '048',
            'active' => true,
            'tips_enabled' => true
        ],
        'UBA' => [
            'name' => 'UNITED BANK FOR AFRICA',
            'code' => 'UNBFTZTZ',
            'fsp_id' => '038',
            'active' => true,
            'tips_enabled' => true
        ],
        'UCHUMI' => [
            'name' => 'UCHUMI COMMERCIAL BANK',
            'code' => 'UCCTTZTZ',
            'fsp_id' => '032',
            'active' => true,
            'tips_enabled' => true
        ]
    ],

    'mobile_wallets' => [
        'MPESA' => [
            'name' => 'Vodacom Mpesa',
            'code' => 'VMCASHIN',
            'fsp_id' => '503',
            'active' => true,
            'tips_enabled' => true,
            'tested' => true,
            'working' => true
        ],
        'TIGOPESA' => [
            'name' => 'Millicom Tanzania Mobile Solution Limited',
            'code' => 'TPCASHIN',
            'fsp_id' => '501',
            'active' => true,
            'tips_enabled' => true,
            'tested' => true,
            'working' => false // Timeout in UAT
        ],
        'AIRTEL' => [
            'name' => 'Airtel Money',
            'code' => 'AMCASHIN',
            'fsp_id' => '504',
            'active' => true,
            'tips_enabled' => true
        ],
        'HALOPESA' => [
            'name' => 'VIETTEL TANZANIA PLC',
            'code' => 'HPCASHIN',
            'fsp_id' => '506',
            'active' => true,
            'tips_enabled' => true
        ],
        'AZAMPESA' => [
            'name' => 'AZAM PESA',
            'code' => 'APCASHIN',
            'fsp_id' => '511',
            'active' => true,
            'tips_enabled' => true
        ],
        'TPESA' => [
            'name' => 'TTCL',
            'code' => 'TTCLPPS',
            'fsp_id' => '505',
            'active' => true,
            'tips_enabled' => true
        ],
        'ZANTEL' => [
            'name' => 'Zantel - Tigo',
            'code' => 'ZPCASHIN',
            'fsp_id' => '501',
            'active' => true,
            'tips_enabled' => true
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Accounts
    |--------------------------------------------------------------------------
    */
    'test_accounts' => [
        'banks' => [
            'CRDB' => [
                'account' => '12334567789',
                'name' => 'SAMWEL MARWA JUMA',
                'working' => true
            ]
        ],
        'wallets' => [
            'MPESA' => [
                'phone' => '0748045601',
                'name' => 'TEST Lab',
                'working' => true
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */
    'stats' => [
        'total_banks' => 29,
        'total_wallets' => 7,
        'total_fsps' => 36,
        'tested_banks' => 2,
        'tested_wallets' => 2,
        'working_banks' => 1,
        'working_wallets' => 1,
        'last_updated' => '2025-09-03 15:57:37'
    ]
];