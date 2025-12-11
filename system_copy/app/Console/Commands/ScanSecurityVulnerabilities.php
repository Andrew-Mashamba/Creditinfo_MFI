<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Security Vulnerability Scanner
 *
 * Scans codebase for common security vulnerabilities:
 * - SQL Injection (raw SQL)
 * - XSS (unescaped output)
 * - Command Injection (shell execution)
 * - Path Traversal
 * - Insecure Deserialization
 * - Hardcoded Secrets
 * - Weak Cryptography
 *
 * @package App\Console\Commands
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class ScanSecurityVulnerabilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:scan
                            {--path= : Specific path to scan}
                            {--detail : Show detailed output}
                            {--fix : Attempt to fix issues (not implemented yet)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan codebase for security vulnerabilities';

    /**
     * Vulnerability patterns to search for
     *
     * @var array
     */
    protected $vulnerabilityPatterns = [];

    /**
     * Found vulnerabilities
     *
     * @var array
     */
    protected $vulnerabilities = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->initializePatterns();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Security Vulnerability Scanner');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $path = $this->option('path') ?: app_path();

        $this->info("Scanning path: {$path}");
        $this->newLine();

        $files = $this->getPhpFiles($path);

        $this->info("Found " . count($files) . " PHP files to scan");
        $this->newLine();

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $this->scanFile($file);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displayResults();

        Log::info('Security scan completed', [
            'files_scanned' => count($files),
            'vulnerabilities_found' => count($this->vulnerabilities),
        ]);

        return count($this->vulnerabilities) > 0 ? 1 : 0;
    }

    /**
     * Initialize vulnerability patterns
     *
     * @return void
     */
    protected function initializePatterns()
    {
        $this->vulnerabilityPatterns = [
            'sql_injection' => [
                'name' => 'SQL Injection',
                'severity' => 'CRITICAL',
                'patterns' => [
                    '/DB::raw\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'DB::raw() with variable concatenation',
                    '/whereRaw\s*\(\s*["\'].*?\$.*?["\']/i' => 'whereRaw() with variable concatenation',
                    '/selectRaw\s*\(\s*["\'].*?\$.*?["\']/i' => 'selectRaw() with variable concatenation',
                    '/orderByRaw\s*\(\s*["\'].*?\$.*?["\']/i' => 'orderByRaw() with variable concatenation',
                    '/havingRaw\s*\(\s*["\'].*?\$.*?["\']/i' => 'havingRaw() with variable concatenation',
                ],
            ],

            'xss' => [
                'name' => 'Cross-Site Scripting (XSS)',
                'severity' => 'HIGH',
                'patterns' => [
                    '/\{\!\!\s*\$.*?\!\!\}/i' => 'Unescaped blade output {!! $var !!}',
                    '/<\?=\s*\$.*?\?>/i' => 'Unescaped PHP echo <?= $var ?>',
                    '/echo\s+\$_(GET|POST|REQUEST)\[/i' => 'Direct echo of user input',
                    '/print\s+\$_(GET|POST|REQUEST)\[/i' => 'Direct print of user input',
                ],
            ],

            'command_injection' => [
                'name' => 'Command Injection',
                'severity' => 'CRITICAL',
                'patterns' => [
                    '/exec\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'exec() with variable concatenation',
                    '/shell_exec\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'shell_exec() with variable concatenation',
                    '/system\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'system() with variable concatenation',
                    '/passthru\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'passthru() with variable concatenation',
                    '/popen\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'popen() with variable concatenation',
                    '/proc_open\s*\(\s*["\'].*?\$.*?["\']\s*\)/i' => 'proc_open() with variable concatenation',
                    '/`.*?\$.*?`/i' => 'Backtick execution with variable',
                ],
            ],

            'path_traversal' => [
                'name' => 'Path Traversal',
                'severity' => 'HIGH',
                'patterns' => [
                    '/file_get_contents\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'file_get_contents() with user input',
                    '/fopen\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'fopen() with user input',
                    '/readfile\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'readfile() with user input',
                    '/include\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'include() with user input',
                    '/require\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'require() with user input',
                ],
            ],

            'insecure_deserialization' => [
                'name' => 'Insecure Deserialization',
                'severity' => 'HIGH',
                'patterns' => [
                    '/unserialize\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'unserialize() with user input',
                    '/unserialize\s*\(\s*\$request->/i' => 'unserialize() with request data',
                ],
            ],

            'hardcoded_secrets' => [
                'name' => 'Hardcoded Secrets',
                'severity' => 'HIGH',
                'patterns' => [
                    '/(password|passwd|pwd)\s*=\s*["\'][^$env{][^"\']{8,}["\']/i' => 'Hardcoded password',
                    '/(api_key|apikey|api_secret)\s*=\s*["\'][^$env{][^"\']{16,}["\']/i' => 'Hardcoded API key',
                    '/(secret|secret_key)\s*=\s*["\'][^$env{][^"\']{16,}["\']/i' => 'Hardcoded secret',
                    '/mysql:\/\/[^:]+:[^@]+@/i' => 'Hardcoded database credentials in connection string',
                ],
            ],

            'weak_crypto' => [
                'name' => 'Weak Cryptography',
                'severity' => 'MEDIUM',
                'patterns' => [
                    '/md5\s*\(/i' => 'MD5 is cryptographically broken',
                    '/sha1\s*\(/i' => 'SHA1 is cryptographically weak',
                    '/mcrypt_/i' => 'mcrypt is deprecated',
                    '/rand\s*\(/i' => 'rand() is not cryptographically secure',
                    '/mt_rand\s*\(/i' => 'mt_rand() is not cryptographically secure (use random_int)',
                ],
            ],

            'mass_assignment' => [
                'name' => 'Mass Assignment Vulnerability',
                'severity' => 'MEDIUM',
                'patterns' => [
                    '/protected\s+\$guarded\s*=\s*\[\s*\]/i' => 'Empty $guarded array (use $fillable instead)',
                ],
            ],

            'open_redirect' => [
                'name' => 'Open Redirect',
                'severity' => 'MEDIUM',
                'patterns' => [
                    '/redirect\s*\(\s*\$_(GET|POST|REQUEST)\[/i' => 'Redirect with user input',
                    '/header\s*\(\s*["\']Location:.*?\$_(GET|POST|REQUEST)\[/i' => 'Location header with user input',
                ],
            ],

            'debug_mode' => [
                'name' => 'Debug Mode Enabled',
                'severity' => 'LOW',
                'patterns' => [
                    '/var_dump\s*\(/i' => 'var_dump() in production code',
                    '/print_r\s*\(/i' => 'print_r() in production code (use logging)',
                    '/dd\s*\(/i' => 'dd() debug helper',
                    '/dump\s*\(/i' => 'dump() debug helper',
                ],
            ],
        ];
    }

    /**
     * Get all PHP files in path
     *
     * @param  string  $path
     * @return array
     */
    protected function getPhpFiles(string $path): array
    {
        if (!File::exists($path)) {
            $this->error("Path does not exist: {$path}");
            return [];
        }

        if (File::isFile($path)) {
            return [$path];
        }

        return File::allFiles($path);
    }

    /**
     * Scan a single file for vulnerabilities
     *
     * @param  string|\SplFileInfo  $file
     * @return void
     */
    protected function scanFile($file)
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $fileName = is_string($file) ? basename($file) : $file->getFilename();

        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
            return;
        }

        $content = File::get($filePath);
        $lines = explode("\n", $content);

        foreach ($this->vulnerabilityPatterns as $category => $config) {
            foreach ($config['patterns'] as $pattern => $description) {
                $lineNumber = 0;

                foreach ($lines as $line) {
                    $lineNumber++;

                    if (preg_match($pattern, $line, $matches)) {
                        $this->vulnerabilities[] = [
                            'category' => $category,
                            'name' => $config['name'],
                            'severity' => $config['severity'],
                            'description' => $description,
                            'file' => $filePath,
                            'line' => $lineNumber,
                            'code' => trim($line),
                        ];

                        if ($this->option('detail')) {
                            $this->newLine();
                            $this->warn("Found: {$config['name']}");
                            $this->line("File: {$filePath}:{$lineNumber}");
                            $this->line("Code: " . trim($line));
                        }
                    }
                }
            }
        }
    }

    /**
     * Display scan results
     *
     * @return void
     */
    protected function displayResults()
    {
        if (empty($this->vulnerabilities)) {
            $this->info('✓ No vulnerabilities found!');
            return;
        }

        $this->error('✗ Found ' . count($this->vulnerabilities) . ' potential vulnerabilities');
        $this->newLine();

        // Group by severity
        $bySeverity = [
            'CRITICAL' => [],
            'HIGH' => [],
            'MEDIUM' => [],
            'LOW' => [],
        ];

        foreach ($this->vulnerabilities as $vuln) {
            $bySeverity[$vuln['severity']][] = $vuln;
        }

        foreach ($bySeverity as $severity => $vulns) {
            if (empty($vulns)) {
                continue;
            }

            $this->newLine();
            $color = $this->getSeverityColor($severity);
            $this->line("<{$color}>━━━ {$severity} SEVERITY (" . count($vulns) . ") ━━━</{$color}>");

            // Group by category
            $byCategory = [];
            foreach ($vulns as $vuln) {
                $byCategory[$vuln['name']][] = $vuln;
            }

            foreach ($byCategory as $category => $categoryVulns) {
                $this->newLine();
                $this->line("<{$color}>{$category} (" . count($categoryVulns) . ")</{$color}>");

                $count = 0;
                foreach ($categoryVulns as $vuln) {
                    $count++;
                    if ($count > 5 && !$this->option('detail')) {
                        $remaining = count($categoryVulns) - 5;
                        $this->line("  ... and {$remaining} more (use --detail to see all)");
                        break;
                    }

                    $relativePath = str_replace(base_path() . '/', '', $vuln['file']);
                    $this->line("  • {$relativePath}:{$vuln['line']}");
                    $this->line("    {$vuln['description']}");

                    if ($this->option('detail')) {
                        $this->line("    Code: {$vuln['code']}");
                    }
                }
            }
        }

        $this->newLine(2);
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('SUMMARY');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->table(
            ['Severity', 'Count'],
            [
                ['<fg=red>CRITICAL</>', count($bySeverity['CRITICAL'])],
                ['<fg=red>HIGH</>', count($bySeverity['HIGH'])],
                ['<fg=yellow>MEDIUM</>', count($bySeverity['MEDIUM'])],
                ['<fg=blue>LOW</>', count($bySeverity['LOW'])],
            ]
        );

        $this->newLine();
        $this->warn('⚠ Review and fix these vulnerabilities before deploying to production');
        $this->newLine();
    }

    /**
     * Get color for severity level
     *
     * @param  string  $severity
     * @return string
     */
    protected function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'CRITICAL' => 'fg=red;options=bold',
            'HIGH' => 'fg=red',
            'MEDIUM' => 'fg=yellow',
            'LOW' => 'fg=blue',
            default => 'fg=white',
        };
    }
}
