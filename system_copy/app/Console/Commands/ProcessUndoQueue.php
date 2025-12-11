<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UndoSendService;

class ProcessUndoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-undo-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process emails that have passed their undo window';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $undoService = new UndoSendService();
        $processedCount = $undoService->processUndoQueue();
        
        if ($processedCount > 0) {
            $this->info("Processed {$processedCount} emails after undo window.");
        }
        
        return 0;
    }
}