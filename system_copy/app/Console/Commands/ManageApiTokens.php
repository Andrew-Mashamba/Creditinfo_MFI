<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiTokenService;
use App\Models\ApiKey;

/**
 * Manage API Tokens Command
 *
 * Command-line interface for managing API tokens
 *
 * @package App\Console\Commands
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class ManageApiTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:token
                            {action : Action to perform (generate|rotate|revoke|list|stats)}
                            {--client= : Client name}
                            {--id= : Token ID}
                            {--scopes=* : Token scopes (e.g., transactions.read users.write)}
                            {--expires= : Expiration in days (default: 30)}
                            {--rate-limit= : Rate limit (requests per hour)}
                            {--reason= : Revocation reason}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage API tokens (generate, rotate, revoke, list)';

    /**
     * API Token Service
     *
     * @var ApiTokenService
     */
    protected $tokenService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ApiTokenService $tokenService)
    {
        parent::__construct();
        $this->tokenService = $tokenService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'generate':
                return $this->generateToken();

            case 'rotate':
                return $this->rotateToken();

            case 'revoke':
                return $this->revokeToken();

            case 'list':
                return $this->listTokens();

            case 'stats':
                return $this->showStats();

            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: generate, rotate, revoke, list, stats');
                return 1;
        }
    }

    /**
     * Generate a new API token
     *
     * @return int
     */
    protected function generateToken(): int
    {
        $clientName = $this->option('client');

        if (!$clientName) {
            $clientName = $this->ask('Client name');
        }

        $scopes = $this->option('scopes');

        if (empty($scopes)) {
            $this->info('Available scopes: transactions.read, transactions.write, users.read, users.write, reports.read, admin, *');
            $scopesInput = $this->ask('Scopes (comma-separated)', 'transactions.read');
            $scopes = array_map('trim', explode(',', $scopesInput));
        }

        $expiresInDays = $this->option('expires') ?? 30;
        $rateLimit = $this->option('rate-limit') ?? 1000;

        $metadata = [
            'rate_limit' => (int) $rateLimit,
            'created_by' => 'CLI',
            'created_at' => now()->toIso8601String(),
        ];

        $result = $this->tokenService->generateToken(
            $clientName,
            $scopes,
            (int) $expiresInDays,
            $metadata
        );

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('API TOKEN GENERATED');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->line("Client Name: {$clientName}");
        $this->line("Token ID: {$result['api_key']->id}");
        $this->line("Scopes: " . implode(', ', $scopes));
        $this->line("Rate Limit: {$rateLimit} requests/hour");
        $this->line("Expires: {$result['api_key']->expires_at}");
        $this->newLine();
        $this->warn('⚠️  IMPORTANT: Store this token securely. It will NOT be shown again!');
        $this->newLine();
        $this->info("Token: {$result['token']}");
        $this->newLine();
        $this->line('Use in API requests:');
        $this->line('  Authorization: Bearer ' . $result['token']);
        $this->line('  or');
        $this->line('  X-API-Key: ' . $result['token']);
        $this->newLine();

        return 0;
    }

    /**
     * Rotate an existing API token
     *
     * @return int
     */
    protected function rotateToken(): int
    {
        $tokenId = $this->option('id');

        if (!$tokenId) {
            $tokenId = $this->ask('Token ID to rotate');
        }

        $apiKey = ApiKey::find($tokenId);

        if (!$apiKey) {
            $this->error("Token not found: {$tokenId}");
            return 1;
        }

        $this->info("Rotating token for client: {$apiKey->client_name}");

        $expiresInDays = $this->option('expires') ?? 30;

        $result = $this->tokenService->rotateToken($apiKey, (int) $expiresInDays);

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('TOKEN ROTATED');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->line("Old Token ID: {$apiKey->id} (revoked)");
        $this->line("New Token ID: {$result['api_key']->id}");
        $this->line("Expires: {$result['api_key']->expires_at}");
        $this->newLine();
        $this->warn('⚠️  The old token has been revoked and is no longer valid.');
        $this->warn('⚠️  Update your application with the new token:');
        $this->newLine();
        $this->info("New Token: {$result['token']}");
        $this->newLine();

        return 0;
    }

    /**
     * Revoke an API token
     *
     * @return int
     */
    protected function revokeToken(): int
    {
        $tokenId = $this->option('id');

        if (!$tokenId) {
            $tokenId = $this->ask('Token ID to revoke');
        }

        $apiKey = ApiKey::find($tokenId);

        if (!$apiKey) {
            $this->error("Token not found: {$tokenId}");
            return 1;
        }

        $reason = $this->option('reason') ?? 'Manual revocation';

        if ($this->confirm("Revoke token for {$apiKey->client_name}?")) {
            $this->tokenService->revokeToken($apiKey, $reason);

            $this->info('✓ Token revoked successfully');
            $this->line("Client: {$apiKey->client_name}");
            $this->line("Reason: {$reason}");

            return 0;
        }

        $this->info('Revocation cancelled');
        return 0;
    }

    /**
     * List all API tokens
     *
     * @return int
     */
    protected function listTokens(): int
    {
        $tokens = ApiKey::orderBy('created_at', 'desc')->get();

        if ($tokens->isEmpty()) {
            $this->warn('No API tokens found');
            return 0;
        }

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('API TOKENS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $rows = [];

        foreach ($tokens as $token) {
            $scopes = is_string($token->scopes) ? json_decode($token->scopes, true) : $token->scopes;
            $scopesStr = is_array($scopes) ? implode(', ', $scopes) : 'none';

            $status = $token->is_active ? '✓ Active' : '✗ Revoked';

            if ($token->is_active && $this->tokenService->isTokenExpired($token)) {
                $status = '⚠ Expired';
            }

            $rows[] = [
                $token->id,
                $token->client_name,
                $status,
                $scopesStr,
                $token->expires_at ? $token->expires_at->format('Y-m-d') : 'Never',
                $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
            ];
        }

        $this->table(
            ['ID', 'Client', 'Status', 'Scopes', 'Expires', 'Last Used'],
            $rows
        );

        return 0;
    }

    /**
     * Show token usage statistics
     *
     * @return int
     */
    protected function showStats(): int
    {
        $tokenId = $this->option('id');

        if (!$tokenId) {
            $tokenId = $this->ask('Token ID for statistics');
        }

        $apiKey = ApiKey::find($tokenId);

        if (!$apiKey) {
            $this->error("Token not found: {$tokenId}");
            return 1;
        }

        $stats = $this->tokenService->getTokenUsageStats($apiKey, 30);

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('TOKEN USAGE STATISTICS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->line("Client: {$stats['client_name']}");
        $this->line("Token ID: {$stats['key_id']}");
        $this->line("Total Usage: {$stats['total_usage']} requests");
        $this->line("Last Used: " . ($stats['last_used_at'] ? $stats['last_used_at']->diffForHumans() : 'Never'));
        $this->newLine();

        if (!empty($stats['daily_usage'])) {
            $this->info('Daily Usage (Last 30 Days):');
            $dailyRows = [];

            foreach ($stats['daily_usage'] as $date => $usage) {
                $dailyRows[] = [$date, $usage];
            }

            $this->table(['Date', 'Requests'], $dailyRows);
        } else {
            $this->line('No usage data available');
        }

        return 0;
    }
}
