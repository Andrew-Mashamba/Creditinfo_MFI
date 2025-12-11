<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestTerminalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminal:test {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command for the terminal console';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = $this->argument('message') ?? 'Hello from Terminal!';
        
        $this->info('🧪 Terminal Test Command');
        $this->info('=' . str_repeat('=', 50));
        
        $this->line("Message: {$message}");
        $this->line("Timestamp: " . now()->toIso8601String());
        $this->line("User: " . ($this->getLaravel()->make('auth')->user()->name ?? 'Guest'));
        $this->line("PHP Version: " . PHP_VERSION);
        $this->line("Laravel Version: " . app()->version());
        
        $this->info('');
        $this->info('✅ Terminal command executed successfully!');
        
        return Command::SUCCESS;
    }
}
