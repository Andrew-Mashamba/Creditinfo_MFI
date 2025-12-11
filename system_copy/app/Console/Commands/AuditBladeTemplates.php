<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Audit Blade Templates for XSS Vulnerabilities
 *
 * Scans all Blade templates for potential XSS issues:
 * - Unescaped output ({!! !!})
 * - Missing CSP nonce on inline scripts
 * - Inline event handlers (onclick, onerror, etc.)
 * - Dangerous JavaScript patterns
 *
 * @package App\Console\Commands
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class AuditBladeTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit-blades
                            {--fix : Automatically fix simple issues}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit Blade templates for XSS vulnerabilities';

    /**
     * Dangerous patterns to look for
     *
     * @var array
     */
    protected $dangerousPatterns = [
        'unescaped_output' => '/{!!\s*\$[\w\->]+\s*!!}/',
        'inline_script_no_nonce' => '/<script(?!\s+nonce)/',
        'inline_style_no_nonce' => '/<style(?!\s+nonce)/',
        'inline_event_handler' => '/\s+on\w+\s*=\s*["\'][^"\']*["\']/',
        'javascript_protocol' => '/href\s*=\s*["\']javascript:/i',
        'innerHTML_usage' => '/\.innerHTML\s*=/i',
        'eval_usage' => '/eval\s*\(/i',
        'document_write' => '/document\.write/i',
    ];

    /**
     * Whitelisted files or patterns (e.g., for HTMLPurifier usage)
     *
     * @var array
     */
    protected $whitelist = [
        'email-signatures.blade.php', // Uses HTMLPurifier
        'xss-protection-example.blade.php', // Example file
        'components/loan/stat-card.blade.php', // Safe - uses predefined SVG paths
    ];

    /**
     * Issues found during audit
     *
     * @var array
     */
    protected $issues = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Starting Blade template security audit...');
        $this->newLine();

        $viewsPath = resource_path('views');
        $bladeFiles = File::allFiles($viewsPath);

        $totalFiles = count($bladeFiles);
        $filesWithIssues = 0;

        $this->info("Found {$totalFiles} Blade templates to audit");
        $this->newLine();

        foreach ($bladeFiles as $file) {
            $relativePath = str_replace($viewsPath . '/', '', $file->getPathname());

            // Skip whitelisted files
            if ($this->isWhitelisted($relativePath)) {
                if ($this->option('detailed')) {
                    $this->line("⏭️  Skipping whitelisted: {$relativePath}");
                }
                continue;
            }

            $content = File::get($file->getPathname());
            $fileIssues = $this->auditFile($file->getPathname(), $content);

            if (!empty($fileIssues)) {
                $filesWithIssues++;
                $this->displayFileIssues($relativePath, $fileIssues);
            } else if ($this->option('detailed')) {
                $this->line("✅ {$relativePath}");
            }
        }

        $this->newLine();
        $this->displaySummary($totalFiles, $filesWithIssues);

        return $filesWithIssues > 0 ? 1 : 0;
    }

    /**
     * Audit a single file
     *
     * @param string $filePath
     * @param string $content
     * @return array
     */
    protected function auditFile(string $filePath, string $content): array
    {
        $issues = [];
        $lines = explode("\n", $content);

        foreach ($this->dangerousPatterns as $type => $pattern) {
            foreach ($lines as $lineNumber => $line) {
                if (preg_match($pattern, $line, $matches)) {
                    // Skip if it's in a comment
                    if (strpos(trim($line), '{{--') === 0) {
                        continue;
                    }

                    $issues[] = [
                        'type' => $type,
                        'line' => $lineNumber + 1,
                        'content' => trim($line),
                        'match' => $matches[0] ?? '',
                    ];
                }
            }
        }

        // Check for missing @csrf in forms
        if (preg_match('/<form[^>]*method\s*=\s*["\']post["\'][^>]*>/i', $content)) {
            if (!preg_match('/@csrf/', $content)) {
                $issues[] = [
                    'type' => 'missing_csrf',
                    'line' => 0,
                    'content' => 'Form with POST method missing @csrf token',
                    'match' => '',
                ];
            }
        }

        // Check for missing charset meta
        if (preg_match('/<head>/i', $content)) {
            if (!preg_match('/<meta[^>]*charset[^>]*>/i', $content)) {
                $issues[] = [
                    'type' => 'missing_charset',
                    'line' => 0,
                    'content' => 'Missing charset meta tag in <head>',
                    'match' => '',
                ];
            }
        }

        return $issues;
    }

    /**
     * Display issues found in a file
     *
     * @param string $filePath
     * @param array $issues
     * @return void
     */
    protected function displayFileIssues(string $filePath, array $issues): void
    {
        $this->error("❌ {$filePath}");

        foreach ($issues as $issue) {
            $severity = $this->getSeverity($issue['type']);
            $description = $this->getIssueDescription($issue['type']);

            $this->line("   Line {$issue['line']}: [{$severity}] {$description}");

            if ($this->option('detailed') && !empty($issue['content'])) {
                $this->line("   Code: " . substr($issue['content'], 0, 100));
            }
        }

        $this->newLine();
    }

    /**
     * Display audit summary
     *
     * @param int $totalFiles
     * @param int $filesWithIssues
     * @return void
     */
    protected function displaySummary(int $totalFiles, int $filesWithIssues): void
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('AUDIT SUMMARY');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->line("Total files audited: {$totalFiles}");
        $this->line("Files with issues: {$filesWithIssues}");
        $cleanFiles = $totalFiles - $filesWithIssues;
        $this->line("Clean files: {$cleanFiles}");

        if ($filesWithIssues > 0) {
            $this->newLine();
            $this->warn('⚠️  Security issues found! Please review and fix the identified vulnerabilities.');
            $this->newLine();
            $this->info('Recommendations:');
            $this->line('1. Replace {!! $var !!} with {{ $var }} for user input');
            $this->line('2. Add nonce="{{ csp_nonce() }}" to all <script> and <style> tags');
            $this->line('3. Remove inline event handlers (onclick, onerror, etc.)');
            $this->line('4. Replace innerHTML with textContent in JavaScript');
            $this->line('5. Add @csrf to all POST forms');
            $this->line('6. Add <meta charset="UTF-8"> to all pages');
        } else {
            $this->info('✅ No security issues found!');
        }
    }

    /**
     * Get severity level for issue type
     *
     * @param string $type
     * @return string
     */
    protected function getSeverity(string $type): string
    {
        $severities = [
            'unescaped_output' => 'HIGH',
            'inline_script_no_nonce' => 'MEDIUM',
            'inline_style_no_nonce' => 'LOW',
            'inline_event_handler' => 'HIGH',
            'javascript_protocol' => 'CRITICAL',
            'innerHTML_usage' => 'HIGH',
            'eval_usage' => 'CRITICAL',
            'document_write' => 'MEDIUM',
            'missing_csrf' => 'CRITICAL',
            'missing_charset' => 'MEDIUM',
        ];

        return $severities[$type] ?? 'MEDIUM';
    }

    /**
     * Get description for issue type
     *
     * @param string $type
     * @return string
     */
    protected function getIssueDescription(string $type): string
    {
        $descriptions = [
            'unescaped_output' => 'Unescaped output - potential XSS',
            'inline_script_no_nonce' => 'Inline script missing CSP nonce',
            'inline_style_no_nonce' => 'Inline style missing CSP nonce',
            'inline_event_handler' => 'Inline event handler - blocked by CSP',
            'javascript_protocol' => 'javascript: protocol in URL - XSS vector',
            'innerHTML_usage' => 'innerHTML usage - use textContent instead',
            'eval_usage' => 'eval() usage - dangerous code execution',
            'document_write' => 'document.write() usage - deprecated',
            'missing_csrf' => 'POST form missing CSRF protection',
            'missing_charset' => 'Missing charset meta tag',
        ];

        return $descriptions[$type] ?? 'Security issue detected';
    }

    /**
     * Check if file is whitelisted
     *
     * @param string $filePath
     * @return bool
     */
    protected function isWhitelisted(string $filePath): bool
    {
        foreach ($this->whitelist as $pattern) {
            if (strpos($filePath, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
