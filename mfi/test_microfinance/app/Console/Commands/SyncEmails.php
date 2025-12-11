<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImapService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SyncEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:sync 
                            {--user= : Sync emails for a specific user ID}
                            {--email= : Sync emails for a specific email address}
                            {--all : Sync emails for all users}
                            {--server= : Email server to use (default: zima)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync emails from IMAP server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!config('email-servers.sync.enabled')) {
            $this->error('Email sync is disabled. Enable it in the configuration.');
            return 1;
        }

        $server = $this->option('server') ?? config('email-servers.default');
        
        try {
            if ($this->option('all')) {
                $this->syncAllUsers($server);
            } elseif ($userId = $this->option('user')) {
                $this->syncUserById($userId, $server);
            } elseif ($email = $this->option('email')) {
                $this->syncUserByEmail($email, $server);
            } else {
                $this->error('Please specify --user, --email, or --all option.');
                return 1;
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error syncing emails: ' . $e->getMessage());
            Log::channel('email')->error('Email sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Sync emails for all users
     */
    protected function syncAllUsers($server)
    {
        $users = User::whereNotNull('email')->get();
        $this->info("Syncing emails for {$users->count()} users...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            try {
                $this->syncUserEmails($user, $server);
                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Failed to sync emails for {$user->email}: " . $e->getMessage());
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Email sync completed for all users.');
    }

    /**
     * Sync emails for a specific user by ID
     */
    protected function syncUserById($userId, $server)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return;
        }
        
        $this->syncUserEmails($user, $server);
    }

    /**
     * Sync emails for a specific user by email
     */
    protected function syncUserByEmail($email, $server)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return;
        }
        
        $this->syncUserEmails($user, $server);
    }

    /**
     * Sync emails for a user
     */
    protected function syncUserEmails($user, $server)
    {
        $this->info("Syncing emails for {$user->email}...");
        
        try {
            $imapService = new ImapService($server);
            $imapService->connect();
            
            // Show folder list
            $folders = $imapService->getFolders();
            $this->info("Available folders:");
            foreach ($folders as $folder) {
                $this->line("  - {$folder['name']} ({$folder['messages']} messages, {$folder['unread']} unread)");
            }
            
            // Sync emails
            $syncedCount = $imapService->syncEmails($user->id);
            
            $imapService->disconnect();
            
            $this->info("Synced {$syncedCount} emails for {$user->email}");
            
        } catch (\Exception $e) {
            throw new \Exception("Failed to sync emails for {$user->email}: " . $e->getMessage());
        }
    }
}