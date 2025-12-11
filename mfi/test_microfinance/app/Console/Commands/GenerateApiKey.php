<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:generate-key 
                            {client_name : The name of the client}
                            {--description= : Description of the API key}
                            {--rate-limit=1000 : Rate limit per hour}
                            {--permissions=* : Permissions (comma-separated)}
                            {--allowed-ips= : Allowed IPs (comma-separated)}
                            {--expires-in=365 : Days until expiration}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a new API key for external services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientName = $this->argument('client_name');
        $description = $this->option('description') ?? "API key for {$clientName}";
        $rateLimit = (int) $this->option('rate-limit');
        $permissions = $this->parsePermissions($this->option('permissions'));
        $allowedIps = $this->parseAllowedIps($this->option('allowed-ips'));
        $expiresIn = (int) $this->option('expires-in');

        try {
            $apiKey = ApiKey::create([
                'client_name' => $clientName,
                'description' => $description,
                'rate_limit' => $rateLimit,
                'permissions' => $permissions,
                'allowed_ips' => $allowedIps,
                'expires_at' => $expiresIn > 0 ? now()->addDays($expiresIn) : null,
                'created_by' => 1, // System user
            ]);

            $this->info('API Key generated successfully!');
            $this->line('');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Client Name', $apiKey->client_name],
                    ['API Key', $apiKey->key],
                    ['Masked Key', $apiKey->masked_key],
                    ['Rate Limit', $apiKey->rate_limit . ' requests/hour'],
                    ['Permissions', implode(', ', $apiKey->permissions ?? [])],
                    ['Allowed IPs', implode(', ', $apiKey->allowed_ips ?? ['All IPs'])],
                    ['Expires At', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : 'Never'],
                    ['Created At', $apiKey->created_at->format('Y-m-d H:i:s')],
                ]
            );

            $this->line('');
            $this->warn('⚠️  IMPORTANT: Save this API key securely. It will not be shown again!');
            $this->line('');

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to generate API key: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Parse permissions from string
     */
    protected function parsePermissions($permissions)
    {
        if (empty($permissions) || $permissions === ['*']) {
            return ['*']; // Wildcard permission
        }

        if (is_string($permissions)) {
            return array_map('trim', explode(',', $permissions));
        }

        return $permissions;
    }

    /**
     * Parse allowed IPs from string
     */
    protected function parseAllowedIps($allowedIps)
    {
        if (empty($allowedIps)) {
            return null; // Allow all IPs
        }

        if (is_string($allowedIps)) {
            return array_map('trim', explode(',', $allowedIps));
        }

        return $allowedIps;
    }
} 