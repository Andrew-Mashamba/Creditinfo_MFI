<?php

namespace App\Http\Livewire\ProfileSetting;

use Livewire\Component;
use App\Services\ResellerApiService;
use App\Models\Domain;
use Illuminate\Support\Facades\Log;

class DomainManagement extends Component
{
    public $domains = [];
    public $searchDomain = '';
    public $showRegistrationForm = false;
    public $showRenewalForm = false;
    public $showDomainDetails = false;
    public $selectedDomain = null;
    
    // Domain registration properties
    public $domainName = '';
    public $registrantName = '';
    public $registrantOrganization = '';
    public $registrantEmail = '';
    public $registrantPhone = '';
    public $registrantAddress = '';
    public $city = '';
    public $country = 'TZ';
    public $adminName = '';
    public $adminEmail = '';
    public $adminPhone = '';
    public $registrationPeriod = 1;
    public $nameservers = ['', ''];
    
    // Domain availability check
    public $availabilityResult = null;
    public $checkingAvailability = false;
    
    // Domain registration result
    public $registrationResult = null;
    public $registeringDomain = false;
    
    // Domain renewal result
    public $renewalResult = null;
    public $renewingDomain = false;
    
    // Renewal properties
    public $renewalPeriod = 1;
    
    protected $rules = [
        'domainName' => 'required|string|max:255',
        'registrantName' => 'required|string|max:255',
        'registrantOrganization' => 'nullable|string|max:255',
        'registrantEmail' => 'required|email|max:255',
        'registrantPhone' => 'required|string|max:20',
        'registrantAddress' => 'required|string',
        'city' => 'required|string|max:100',
        'country' => 'required|string|size:2',
        'adminName' => 'nullable|string|max:255',
        'adminEmail' => 'nullable|email|max:255',
        'adminPhone' => 'nullable|string|max:20',
        'registrationPeriod' => 'required|integer|min:1|max:10',
        'nameservers.*' => 'required|string|max:255'
    ];

    protected $messages = [
        'domainName.required' => 'Domain name is required.',
        'registrantName.required' => 'Registrant name is required.',
        'registrantEmail.required' => 'Registrant email is required.',
        'registrantEmail.email' => 'Please enter a valid email address.',
        'registrantPhone.required' => 'Registrant phone is required.',
        'registrantAddress.required' => 'Registrant address is required.',
        'city.required' => 'City is required.',
        'country.required' => 'Country is required.',
        'adminEmail.email' => 'Please enter a valid admin email address.',
        'registrationPeriod.required' => 'Registration period is required.',
        'registrationPeriod.min' => 'Registration period must be at least 1 year.',
        'registrationPeriod.max' => 'Registration period cannot exceed 10 years.',
        'nameservers.*.required' => 'Nameserver is required.',
    ];

    public function mount()
    {
        $this->loadDomains();
    }
    
    public function loadDomains()
    {
        $this->domains = Domain::orderBy('expiry_date', 'asc')->get();
    }
    
    public function checkDomainAvailability()
    {
        $this->validate(['searchDomain' => 'required|string']);
        
        $this->checkingAvailability = true;
        $this->availabilityResult = null;
        
        try {
            $apiService = new ResellerApiService();
            $result = $apiService->checkDomainAvailability($this->searchDomain);
            
            if ($result['status'] === 'success') {
                $this->availabilityResult = $result['data'];
                $this->dispatchBrowserEvent('domain-checked', [
                    'available' => $result['data']['available'],
                    'domain' => $result['data']['domain'],
                    'code' => $result['data']['code'],
                    'message' => $result['data']['message']
                ]);
            } elseif ($result['status'] === 'error') {
                $this->dispatchBrowserEvent('error', ['message' => $result['message']]);
            }
        } catch (\Exception $e) {
            Log::error('Domain availability check failed', [
                'domain' => $this->searchDomain,
                'error' => $e->getMessage()
            ]);
            $this->dispatchBrowserEvent('error', ['message' => $e->getMessage()]);
        } finally {
            $this->checkingAvailability = false;
        }
    }
    
    public function registerDomain()
    {
        $this->validate();
        
        $this->registeringDomain = true;
        $this->registrationResult = null;
        
        try {
            $apiService = new ResellerApiService();
            $domainData = [
                'domainName' => $this->domainName,
                'period' => $this->registrationPeriod,
                'nameservers' => array_filter($this->nameservers),
                'registrantInfo' => [
                    'name' => $this->registrantName,
                    'organization' => $this->registrantOrganization,
                    'email' => $this->registrantEmail,
                    'phone' => $this->registrantPhone,
                    'address' => $this->registrantAddress,
                    'city' => $this->city,
                    'country' => $this->country
                ],
                'adminInfo' => [
                    'name' => $this->adminName ?: $this->registrantName,
                    'email' => $this->adminEmail ?: $this->registrantEmail,
                    'phone' => $this->adminPhone ?: $this->registrantPhone
                ]
            ];
            
            $result = $apiService->registerDomain($domainData);
 
            if ($result['status'] === 'success') {
                // Save to database
                Domain::create([
                    'domain_name' => $result['data']['domain'],
                    'registrant_name' => $this->registrantName,
                    'registrant_organization' => $this->registrantOrganization,
                    'registrant_email' => $this->registrantEmail,
                    'registrant_phone' => $this->registrantPhone,
                    'registrant_address' => $this->registrantAddress,
                    'city' => $this->city,
                    'country' => $this->country,
                    'admin_name' => $this->adminName ?: $this->registrantName,
                    'admin_email' => $this->adminEmail ?: $this->registrantEmail,
                    'admin_phone' => $this->adminPhone ?: $this->registrantPhone,
                    'nameservers' => array_filter($this->nameservers),
                    'registration_date' => $result['data']['registrationDate'],
                    'expiry_date' => $result['data']['expiryDate'],
                    'amount' => $result['data']['amount'],
                    'currency' => $result['data']['currency'],
                    'transaction_id' => $result['data']['transactionId'],
                    'registration_period' => $this->registrationPeriod,
                    'status' => 'active'
                ]);
                
                $this->registrationResult = [
                    'success' => true,
                    'domain' => $result['data']['domain'],
                    'message' => 'Domain registered successfully!',
                    'registrationDate' => $result['data']['registrationDate'],
                    'expiryDate' => $result['data']['expiryDate'],
                    'amount' => $result['data']['amount'],
                    'currency' => $result['data']['currency'],
                    'transactionId' => $result['data']['transactionId']
                ];
                
                $this->resetForm();
                $this->loadDomains();
                $this->showRegistrationForm = false;
                
                $this->dispatchBrowserEvent('domain-registered', [
                    'success' => true,
                    'domain' => $result['data']['domain'],
                    'message' => 'Domain registered successfully!'
                ]);
            } elseif ($result['status'] === 'error') {
                $this->registrationResult = [
                    'success' => false,
                    'message' => $result['message'],
                    'code' => $result['code'] ?? null
                ];
                $this->dispatchBrowserEvent('error', ['message' => $result['message']]);
            }
        } catch (\Exception $e) {
            Log::error('Domain registration failed', [
                'domain' => $this->domainName,
                'error' => $e->getMessage()
            ]);
            $this->registrationResult = [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
            $this->dispatchBrowserEvent('error', ['message' => $e->getMessage()]);
        } finally {
            $this->registeringDomain = false;
        }
    }
    
    public function renewDomain($domainId)
    {
        $domain = Domain::findOrFail($domainId);
        
        $this->renewingDomain = true;
        $this->renewalResult = null;
        
        try {
            $apiService = new ResellerApiService();
            $result = $apiService->renewDomain($domain->domain_name, $this->renewalPeriod);
            
            if ($result['status'] === 'success') {
                $domain->update([
                    'expiry_date' => $result['data']['expiryDate'],
                    'amount' => $result['data']['amount'],
                    'transaction_id' => $result['data']['transactionId']
                ]);
                
                $this->renewalResult = [
                    'success' => true,
                    'domain' => $domain->domain_name,
                    'message' => 'Domain renewed successfully!',
                    'expiryDate' => $result['data']['expiryDate'],
                    'amount' => $result['data']['amount'],
                    'currency' => $result['data']['currency'] ?? 'USD',
                    'transactionId' => $result['data']['transactionId'],
                    'renewalPeriod' => $this->renewalPeriod
                ];
                
                $this->loadDomains();
                $this->showRenewalForm = false;
                $this->selectedDomain = null;
                
                $this->dispatchBrowserEvent('domain-renewed', [
                    'success' => true,
                    'domain' => $domain->domain_name,
                    'message' => 'Domain renewed successfully!'
                ]);
            } elseif ($result['status'] === 'error') {
                $this->renewalResult = [
                    'success' => false,
                    'domain' => $domain->domain_name,
                    'message' => $result['message'],
                    'code' => $result['code'] ?? null
                ];
                $this->dispatchBrowserEvent('error', ['message' => $result['message']]);
            }
        } catch (\Exception $e) {
            Log::error('Domain renewal failed', [
                'domain' => $domain->domain_name,
                'error' => $e->getMessage()
            ]);
            $this->renewalResult = [
                'success' => false,
                'domain' => $domain->domain_name,
                'message' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ];
            $this->dispatchBrowserEvent('error', ['message' => $e->getMessage()]);
        } finally {
            $this->renewingDomain = false;
        }
    }
    
    public function showRenewalModal($domainId)
    {
        $this->selectedDomain = Domain::findOrFail($domainId);
        $this->showRenewalForm = true;
    }
    
    public function cancelRenewal()
    {
        $this->showRenewalForm = false;
        $this->selectedDomain = null;
        $this->renewalPeriod = 1;
    }
    
    public function showDomainDetails($domainId)
    {
        $this->selectedDomain = Domain::findOrFail($domainId);
        $this->showDomainDetails = true;
    }
    
    public function closeDomainDetails()
    {
        $this->showDomainDetails = false;
        $this->selectedDomain = null;
    }
    
    public function addNameserver()
    {
        $this->nameservers[] = '';
    }
    
    public function removeNameserver($index)
    {
        unset($this->nameservers[$index]);
        $this->nameservers = array_values($this->nameservers);
    }
    
    public function fillFromAvailabilityCheck()
    {
        if ($this->availabilityResult && $this->availabilityResult['available']) {
            $this->domainName = $this->searchDomain;
        }
    }
    
    public function clearRegistrationResult()
    {
        $this->registrationResult = null;
    }
    
    public function clearRenewalResult()
    {
        $this->renewalResult = null;
    }
    
    private function resetForm()
    {
        $this->domainName = '';
        $this->registrantName = '';
        $this->registrantOrganization = '';
        $this->registrantEmail = '';
        $this->registrantPhone = '';
        $this->registrantAddress = '';
        $this->city = '';
        $this->country = 'TZ';
        $this->adminName = '';
        $this->adminEmail = '';
        $this->adminPhone = '';
        $this->registrationPeriod = 1;
        $this->nameservers = ['', ''];
        $this->availabilityResult = null;
        $this->registrationResult = null;
        $this->renewalResult = null;
        $this->searchDomain = '';
    }
    
    public function render()
    {
        return view('livewire.profile-setting.domain-management');
    }
}
