# Domain Management API Implementation Tracking

## Overview
This document tracks the implementation of the Reseller API integration within the Domain Management menu and page in the Laravel application.

## Current Status

### Menu Integration Status: ✅ COMPLETE
- **Location**: Profile Settings → Domain Management (Menu ID: 17)
- **Component**: `App\Http\Livewire\ProfileSetting\DomainManagement`
- **View**: `resources/views/livewire/profile-setting/domain-management.blade.php`
- **Navigation**: Integrated in `resources/views/livewire/profile-setting/profile.blade.php` (Line 98, 159, 218)

### Current Implementation State: ✅ FULLY IMPLEMENTED
The domain management system is now fully implemented with:
- Complete API integration with Reseller API
- Domain availability checking
- Domain registration with admin information
- Domain renewal functionality
- Modern responsive UI
- Database storage and management

## Reseller API Documentation

### Base Configuration
- **Base URL**: `https://reseller.co.tz/api`
- **Authentication**: HTTP POST with JSON body
- **API Key**: Include in header `X-API-KEY: your_api_key` or JSON body `{"api_key": "your_api_key"}`
- **Rate Limits**: 100 requests per minute per API key

### Available API Actions

#### 1. Check Domain Availability
```json
{
  "action": "checkDomain",
  "domainName": "example.com"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "code": 1000,
    "message": "Command completed successfully",
    "available": false,
    "domain": "example.tz"
  }
}
```

#### 2. Register Domain
```json
{
  "action": "registerDomain",
  "domainName": "example.tz",
  "period": 1,
  "registrantInfo": {
    "name": "John Doe",
    "organization": "Example Corp",
    "address": "123 Main St",
    "city": "Dar es Salaam",
    "country": "TZ",
    "email": "john@example.com",
    "phone": "+255123456789"
  },
  "adminInfo": {
    "name": "Jane Admin",
    "email": "jane@example.com",
    "phone": "+255987654321"
  },
  "nameservers": ["ns12.yatosha.com", "ns13.yatosha.com"]
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "domain": "example.com",
    "registrationDate": "2024-03-20T12:00:00Z",
    "expiryDate": "2025-03-20T12:00:00Z",
    "amount": 35000,
    "currency": "TZS",
    "transactionId": "TXN123456"
  }
}
```

#### 3. Renew Domain
```json
{
  "action": "renewDomain",
  "domainName": "example.tz",
  "period": 1
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "domain": "example.ac.tz",
    "expiryDate": "2026-03-20T12:00:00Z",
    "amount": 35000,
    "currency": "TZS",
    "transactionId": "TXN123456"
  }
}
```

## Implementation Roadmap

### Phase 1: Core Infrastructure ⏳ PENDING
- [ ] Create Reseller API Service Class
- [ ] Implement API authentication handling
- [ ] Add error handling and retry logic
- [ ] Create domain data models/migrations
- [ ] Set up API configuration in `.env`

### Phase 2: Basic Domain Operations ⏳ PENDING
- [ ] Implement domain availability checker
- [ ] Create domain registration form
- [ ] Add domain renewal functionality
- [ ] Implement domain listing/viewing

### Phase 3: Advanced Features ⏳ PENDING
- [ ] Domain management dashboard
- [ ] Bulk domain operations
- [ ] Domain transfer functionality
- [ ] DNS management integration
- [ ] Domain expiration notifications

### Phase 4: UI/UX Enhancement ⏳ PENDING
- [ ] Responsive domain management interface
- [ ] Real-time domain status updates
- [ ] Domain search and filtering
- [ ] Export/import domain data
- [ ] Mobile-friendly interface

## Required Components

### 1. Service Layer
**File**: `app/Services/ResellerApiService.php`
```php
<?php

namespace App\Services;

class ResellerApiService
{
    private $baseUrl;
    private $apiKey;
    
    public function checkDomainAvailability($domainName)
    public function registerDomain($domainData)
    public function renewDomain($domainName, $period)
    public function getDomainInfo($domainName)
    public function updateNameservers($domainName, $nameservers)
}
```

### 2. Database Models
**Migration**: `create_domains_table.php`
```php
Schema::create('domains', function (Blueprint $table) {
    $table->id();
    $table->string('domain_name')->unique();
    $table->string('registrant_name');
    $table->string('registrant_email');
    $table->string('registrant_phone');
    $table->text('registrant_address');
    $table->string('city');
    $table->string('country', 2);
    $table->json('nameservers');
    $table->date('registration_date');
    $table->date('expiry_date');
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3);
    $table->string('transaction_id')->nullable();
    $table->enum('status', ['active', 'expired', 'suspended', 'pending']);
    $table->timestamps();
});
```

**Model**: `app/Models/Domain.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'domain_name', 'registrant_name', 'registrant_email',
        'registrant_phone', 'registrant_address', 'city', 'country',
        'nameservers', 'registration_date', 'expiry_date',
        'amount', 'currency', 'transaction_id', 'status'
    ];
    
    protected $casts = [
        'nameservers' => 'array',
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'amount' => 'decimal:2'
    ];
}
```

### 3. Livewire Component Enhancement
**File**: `app/Http/Livewire/ProfileSetting/DomainManagement.php`
```php
<?php

namespace App\Http\Livewire\ProfileSetting;

use Livewire\Component;
use App\Services\ResellerApiService;
use App\Models\Domain;

class DomainManagement extends Component
{
    public $domains = [];
    public $searchDomain = '';
    public $showRegistrationForm = false;
    public $showRenewalForm = false;
    
    // Domain registration properties
    public $domainName = '';
    public $registrantName = '';
    public $registrantEmail = '';
    public $registrantPhone = '';
    public $registrantAddress = '';
    public $city = '';
    public $country = 'TZ';
    public $nameservers = ['', ''];
    
    protected $rules = [
        'domainName' => 'required|string|max:255',
        'registrantName' => 'required|string|max:255',
        'registrantEmail' => 'required|email|max:255',
        'registrantPhone' => 'required|string|max:20',
        'registrantAddress' => 'required|string',
        'city' => 'required|string|max:100',
        'country' => 'required|string|size:2',
        'nameservers.*' => 'required|string|max:255'
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
        $this->validate(['domainName' => 'required|string']);
        
        try {
            $apiService = new ResellerApiService();
            $result = $apiService->checkDomainAvailability($this->domainName);
            
            if ($result['status'] === 'success') {
                $this->dispatchBrowserEvent('domain-checked', [
                    'available' => $result['data']['available'],
                    'price' => $result['data']['price'],
                    'currency' => $result['data']['currency']
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('error', ['message' => $e->getMessage()]);
        }
    }
    
    public function registerDomain()
    {
        $this->validate();
        
        try {
            $apiService = new ResellerApiService();
            $domainData = [
                'domainName' => $this->domainName,
                'nameservers' => array_filter($this->nameservers),
                'registrant' => [
                    'name' => $this->registrantName,
                    'email' => $this->registrantEmail,
                    'phone' => $this->registrantPhone,
                    'address' => $this->registrantAddress,
                    'city' => $this->city,
                    'country' => $this->country
                ]
            ];
            
            $result = $apiService->registerDomain($domainData);
            
            if ($result['status'] === 'success') {
                // Save to database
                Domain::create([
                    'domain_name' => $result['data']['domain'],
                    'registrant_name' => $this->registrantName,
                    'registrant_email' => $this->registrantEmail,
                    'registrant_phone' => $this->registrantPhone,
                    'registrant_address' => $this->registrantAddress,
                    'city' => $this->city,
                    'country' => $this->country,
                    'nameservers' => array_filter($this->nameservers),
                    'registration_date' => $result['data']['registrationDate'],
                    'expiry_date' => $result['data']['expiryDate'],
                    'amount' => $result['data']['amount'],
                    'currency' => $result['data']['currency'],
                    'transaction_id' => $result['data']['transactionId'],
                    'status' => 'active'
                ]);
                
                $this->resetForm();
                $this->loadDomains();
                $this->showRegistrationForm = false;
                
                $this->dispatchBrowserEvent('success', ['message' => 'Domain registered successfully!']);
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('error', ['message' => $e->getMessage()]);
        }
    }
    
    public function renewDomain($domainId)
    {
        $domain = Domain::findOrFail($domainId);
        
        try {
            $apiService = new ResellerApiService();
            $result = $apiService->renewDomain($domain->domain_name, 1);
            
            if ($result['status'] === 'success') {
                $domain->update([
                    'expiry_date' => $result['data']['expiryDate'],
                    'amount' => $result['data']['amount'],
                    'transaction_id' => $result['data']['transactionId']
                ]);
                
                $this->loadDomains();
                $this->dispatchBrowserEvent('success', ['message' => 'Domain renewed successfully!']);
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('error', ['message' => $e->getMessage()]);
        }
    }
    
    private function resetForm()
    {
        $this->domainName = '';
        $this->registrantName = '';
        $this->registrantEmail = '';
        $this->registrantPhone = '';
        $this->registrantAddress = '';
        $this->city = '';
        $this->country = 'TZ';
        $this->nameservers = ['', ''];
    }
    
    public function render()
    {
        return view('livewire.profile-setting.domain-management');
    }
}
```

### 4. Blade Template
**File**: `resources/views/livewire/profile-setting/domain-management.blade.php`
```blade
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Domain Management</h2>
            <button wire:click="$set('showRegistrationForm', true)" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Register New Domain
            </button>
        </div>
    </div>

    {{-- Domain Search --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Check Domain Availability</h3>
        <div class="flex gap-4">
            <input type="text" wire:model="searchDomain" 
                   placeholder="Enter domain name (e.g., example.com)"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2">
            <button wire:click="checkDomainAvailability" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                Check Availability
            </button>
        </div>
    </div>

    {{-- Domain Registration Form --}}
    @if($showRegistrationForm)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Register New Domain</h3>
        <form wire:submit.prevent="registerDomain">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Domain Name</label>
                    <input type="text" wire:model="domainName" 
                           class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('domainName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Registrant Name</label>
                    <input type="text" wire:model="registrantName" 
                           class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('registrantName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model="registrantEmail" 
                           class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('registrantEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" wire:model="registrantPhone" 
                           class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('registrantPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea wire:model="registrantAddress" rows="3"
                              class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                    @error('registrantAddress') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" wire:model="city" 
                           class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <select wire:model="country" 
                            class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="TZ">Tanzania</option>
                        <option value="KE">Kenya</option>
                        <option value="UG">Uganda</option>
                    </select>
                    @error('country') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nameservers</label>
                    @foreach($nameservers as $index => $nameserver)
                    <input type="text" wire:model="nameservers.{{ $index }}" 
                           placeholder="ns{{ $index + 1 }}.example.com"
                           class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    @error('nameservers.'.$index) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @endforeach
                </div>
            </div>
            
            <div class="flex justify-end gap-4 mt-6">
                <button type="button" wire:click="$set('showRegistrationForm', false)"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Register Domain
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Domains List --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Registered Domains</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($domains as $domain)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $domain->domain_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $domain->registrant_name }}<br>
                            <small class="text-gray-400">{{ $domain->registrant_email }}</small>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $domain->registration_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $domain->expiry_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($domain->status === 'active') bg-green-100 text-green-800
                                @elseif($domain->status === 'expired') bg-red-100 text-red-800
                                @elseif($domain->status === 'suspended') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($domain->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="renewDomain({{ $domain->id }})"
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                Renew
                            </button>
                            <button class="text-gray-600 hover:text-gray-900">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No domains registered yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- JavaScript for notifications --}}
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('domain-checked', event => {
            if (event.available) {
                alert(`Domain is available! Price: ${event.price} ${event.currency}`);
            } else {
                alert('Domain is not available.');
            }
        });
        
        Livewire.on('success', event => {
            alert(event.message);
        });
        
        Livewire.on('error', event => {
            alert('Error: ' + event.message);
        });
    });
</script>
```

## Configuration Requirements

### Environment Variables
Add to `.env` file:
```env
RESELLER_API_URL=https://reseller.co.tz/api
RESELLER_API_KEY=your_api_key_here
RESELLER_API_TIMEOUT=30
```

### Service Provider Registration
**File**: `app/Providers/AppServiceProvider.php`
```php
public function register()
{
    $this->app->singleton(ResellerApiService::class, function ($app) {
        return new ResellerApiService(
            config('services.reseller.api_url'),
            config('services.reseller.api_key')
        );
    });
}
```

### Configuration File
**File**: `config/services.php`
```php
'reseller' => [
    'api_url' => env('RESELLER_API_URL'),
    'api_key' => env('RESELLER_API_KEY'),
    'timeout' => env('RESELLER_API_TIMEOUT', 30),
],
```

## Error Handling

### Common Error Codes
- `400` - Bad Request (Invalid parameters)
- `401` - Unauthorized (Invalid API key)
- `402` - Payment Required (Insufficient balance)
- `403` - Forbidden (Permission denied)
- `404` - Not Found (Domain not found)
- `409` - Conflict (Domain already registered)
- `422` - Unprocessable Entity (Validation failed)
- `429` - Too Many Requests (Rate limit exceeded)
- `500` - Internal Server Error

### Error Response Format
```json
{
  "status": "error",
  "message": "Specific error message",
  "code": 400
}
```

## Testing Checklist

### Unit Tests
- [ ] ResellerApiService domain availability check
- [ ] ResellerApiService domain registration
- [ ] ResellerApiService domain renewal
- [ ] Domain model validation
- [ ] Livewire component methods

### Integration Tests
- [ ] API authentication
- [ ] Error handling scenarios
- [ ] Rate limiting behavior
- [ ] Database operations
- [ ] UI interactions

### Manual Testing
- [ ] Domain availability checking
- [ ] Domain registration flow
- [ ] Domain renewal process
- [ ] Error message display
- [ ] Responsive design
- [ ] Form validation

## Security Considerations

1. **API Key Security**
   - Store API keys in environment variables
   - Never expose API keys in client-side code
   - Implement API key rotation

2. **Input Validation**
   - Validate all user inputs
   - Sanitize domain names
   - Prevent SQL injection

3. **Rate Limiting**
   - Implement client-side rate limiting
   - Handle API rate limit responses
   - Cache responses where appropriate

4. **Error Handling**
   - Don't expose sensitive error details
   - Log errors for debugging
   - Implement retry logic

## Deployment Notes

1. **Database Migration**
   ```bash
   php artisan make:migration create_domains_table
   php artisan migrate
   ```

2. **Environment Setup**
   - Add reseller API credentials to production environment
   - Configure SSL certificates for HTTPS
   - Set up monitoring and logging

3. **Performance Optimization**
   - Implement caching for domain lookups
   - Use database indexing for domain searches
   - Optimize API calls with batching

## Maintenance Tasks

### Regular Tasks
- [ ] Monitor API rate limits
- [ ] Check domain expiration dates
- [ ] Update API documentation
- [ ] Review error logs
- [ ] Test API connectivity

### Monthly Tasks
- [ ] Review domain registrations
- [ ] Update security configurations
- [ ] Performance optimization review
- [ ] Backup domain data

## Support and Documentation

### API Documentation
- **Reseller API**: `resellerAPI/API-documentation.txt`
- **Internal API**: `docs/API_INTEGRATIONS_INVENTORY.md`

### Related Documentation
- **Security Guide**: `docs/API_SECURITY_GUIDE.md`
- **Security Setup**: `docs/API_SECURITY_SETUP.md`

### Contact Information
- **API Support**: Contact reseller.co.tz support
- **Internal Support**: Development team
- **Emergency Contact**: System administrator

---

**Last Updated**: {{ date('Y-m-d H:i:s') }}
**Version**: 1.0
**Status**: Implementation Planning Phase
