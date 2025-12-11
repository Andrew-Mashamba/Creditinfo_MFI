<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetLoanDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:documents {loan_id} {--category=} {--group-by-category}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get documents for a specific loan ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $loanId = $this->argument('loan_id');
        $category = $this->option('category');
        $groupByCategory = $this->option('group-by-category');

        $this->info("Getting documents for loan ID: {$loanId}");

        try {
            $query = DB::table('loan_images')->where('loan_id', $loanId);

            if ($category) {
                $query->where('category', $category);
                $this->info("Filtering by category: {$category}");
            }

            $documents = $query->get();

            if ($documents->isEmpty()) {
                $this->warn("No documents found for loan ID: {$loanId}");
                return 0;
            }

            if ($groupByCategory) {
                $this->displayDocumentsGroupedByCategory($documents);
            } else {
                $this->displayDocuments($documents);
            }

            $this->info("Total documents found: {$documents->count()}");
            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display documents in a simple list
     */
    private function displayDocuments($documents)
    {
        $this->table(
            ['ID', 'Filename', 'Category', 'Description', 'File Size', 'URL'],
            $documents->map(function ($doc) {
                return [
                    $doc->id,
                    $doc->filename ?? 'N/A',
                    $doc->category ?? 'N/A',
                    $doc->document_descriptions ?? 'N/A',
                    $this->formatFileSize($doc->file_size ?? 0),
                    $doc->url ?? 'N/A'
                ];
            })->toArray()
        );
    }

    /**
     * Display documents grouped by category
     */
    private function displayDocumentsGroupedByCategory($documents)
    {
        $grouped = $documents->groupBy('category');

        foreach ($grouped as $category => $docs) {
            $this->info("\nCategory: {$category} ({$docs->count()} documents)");
            
            $this->table(
                ['ID', 'Filename', 'Description', 'File Size'],
                $docs->map(function ($doc) {
                    return [
                        $doc->id,
                        $doc->filename ?? 'N/A',
                        $doc->document_descriptions ?? 'N/A',
                        $this->formatFileSize($doc->file_size ?? 0)
                    ];
                })->toArray()
            );
        }
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
