<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;
use GuzzleHttp\Client;

class FSPService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    
    public function __construct()
    {
        $this->baseUrl = config('services.nbc_payments.base_url', 'https://22.32.245.67:443');
        $this->apiKey = config('services.nbc_payments.api_key');
        
        $this->client = new Client([
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 10
        ]);
    }
    
    /**
     * Get all FSPs from config
     */
    public function getAllFSPs()
    {
        return config('fsp_providers');
    }
    
    /**
     * Get all banks
     */
    public function getAllBanks()
    {
        return config('fsp_providers.banks', []);
    }
    
    /**
     * Get all mobile wallets
     */
    public function getAllWallets()
    {
        return config('fsp_providers.mobile_wallets', []);
    }
    
    /**
     * Get bank by code
     */
    public function getBankByCode(string $code)
    {
        $banks = $this->getAllBanks();
        foreach ($banks as $key => $bank) {
            if ($bank['code'] === $code) {
                return array_merge($bank, ['key' => $key]);
            }
        }
        return null;
    }
    
    /**
     * Get wallet by code
     */
    public function getWalletByCode(string $code)
    {
        $wallets = $this->getAllWallets();
        foreach ($wallets as $key => $wallet) {
            if ($wallet['code'] === $code) {
                return array_merge($wallet, ['key' => $key]);
            }
        }
        return null;
    }
    
    /**
     * Get banks for dropdown
     */
    public function getBanksForDropdown()
    {
        $banks = $this->getAllBanks();
        $options = [];
        
        foreach ($banks as $key => $bank) {
            if ($bank['active'] && $bank['fsp_id'] !== '015') { // Exclude NBC (self)
                $working = isset($bank['working']) && $bank['working'] ? ' ✓' : '';
                $options[$bank['code']] = $bank['name'] . $working;
            }
        }
        
        return $options;
    }
    
    /**
     * Get wallets for dropdown
     */
    public function getWalletsForDropdown()
    {
        $wallets = $this->getAllWallets();
        $options = [];
        
        foreach ($wallets as $key => $wallet) {
            if ($wallet['active']) {
                $working = isset($wallet['working']) && $wallet['working'] ? ' ✓' : '';
                $options[$key] = $wallet['name'] . $working;
            }
        }
        
        return $options;
    }
    
    /**
     * Refresh FSPs from API
     */
    public function refreshFSPsFromAPI()
    {
        try {
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $clientRef = 'CREF' . time();
            $isoTimestamp = date('c');
            
            $payload = [
                'serviceName' => 'FSP_RETRIEVAL',
                'clientId' => 'APP_ANDROID',
                'clientRef' => $clientRef,
                'timestamp' => $isoTimestamp
            ];
            
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Trace-Uuid' => 'domestix-' . $uuid,
                'Signature' => 'asdasdasdasd',
                'x-api-key' => 'MDcyNjY2NWVkZDlkYTJmYWZiZTFiODFhNDQ5MWNkNTY3ODZhZjA2NTNiOTMwNzNiODVkMzVlOTNmN2UxZDE5NTUwZjc3M2I5MzQwYmRlZGRiYzdlMjUxMmU5NGUxMmQ4NmQxOGQ1NTIyYmM3YzlkNjYyY2U2ZjE2YjZhNjFkZjU='
            ];
            
            $response = $this->client->post($this->baseUrl . '/domestix/info/api/v2/financial-service-providers', [
                'headers' => $headers,
                'json' => $payload
            ]);
            
            $responseBody = json_decode($response->getBody()->getContents(), true);
            
            if (isset($responseBody['statusCode']) && $responseBody['statusCode'] == 600) {
                // Cache the results
                Cache::put('fsps_from_api', $responseBody['body'], 3600); // Cache for 1 hour
                
                return [
                    'success' => true,
                    'data' => $responseBody['body'],
                    'count' => count($responseBody['body'])
                ];
            }
            
            return [
                'success' => false,
                'error' => $responseBody['message'] ?? 'Failed to retrieve FSPs'
            ];
            
        } catch (Exception $e) {
            Log::error('FSP Retrieval Error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Test FSP lookup
     */
    public function testFSPLookup(string $fspCode, string $accountOrPhone, float $amount = 1000)
    {
        try {
            // Determine if bank or wallet
            $fsp = $this->getBankByCode($fspCode) ?: $this->getWalletByCode($fspCode);
            
            if (!$fsp) {
                return [
                    'success' => false,
                    'error' => 'FSP not found'
                ];
            }
            
            $isBank = isset($fsp['fsp_id']) && strlen($fsp['fsp_id']) === 3 && $fsp['fsp_id'][0] === '0';
            
            if ($isBank) {
                $service = app(ExternalFundsTransferService::class);
                return $service->lookupAccount($accountOrPhone, $fspCode, $amount);
            } else {
                $service = app(MobileWalletTransferService::class);
                $walletKey = $fsp['key'] ?? 'MPESA';
                return $service->lookupWallet($accountOrPhone, $walletKey, $amount);
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get FSP statistics
     */
    public function getStatistics()
    {
        return config('fsp_providers.stats', []);
    }
    
    /**
     * Get test accounts
     */
    public function getTestAccounts()
    {
        return config('fsp_providers.test_accounts', []);
    }
}