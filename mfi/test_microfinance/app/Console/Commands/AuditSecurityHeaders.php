<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Audit Security Headers Command
 *
 * Tests and verifies HTTP security headers are properly configured
 *
 * @package App\Console\Commands
 * @author MFI Management System Security Team
 * @date 2025-10-16
 */
class AuditSecurityHeaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit-headers
                            {url? : URL to test (defaults to APP_URL)}
                            {--detailed : Show detailed analysis}
                            {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit HTTP security headers configuration';

    /**
     * Required security headers
     *
     * @var array
     */
    protected $requiredHeaders = [
        'Content-Security-Policy' => [
            'severity' => 'CRITICAL',
            'description' => 'Prevents XSS and other injection attacks',
            'checks' => ['nonce', 'strict-dynamic', 'default-src'],
        ],
        'Strict-Transport-Security' => [
            'severity' => 'CRITICAL',
            'description' => 'Forces HTTPS connections',
            'checks' => ['max-age', 'includeSubDomains'],
        ],
        'X-Frame-Options' => [
            'severity' => 'HIGH',
            'description' => 'Prevents clickjacking attacks',
            'checks' => ['DENY', 'SAMEORIGIN'],
        ],
        'X-Content-Type-Options' => [
            'severity' => 'HIGH',
            'description' => 'Prevents MIME type sniffing',
            'checks' => ['nosniff'],
        ],
        'Referrer-Policy' => [
            'severity' => 'MEDIUM',
            'description' => 'Controls referrer information leakage',
            'checks' => ['no-referrer', 'strict-origin'],
        ],
        'Permissions-Policy' => [
            'severity' => 'MEDIUM',
            'description' => 'Restricts browser features',
            'checks' => ['camera', 'microphone', 'geolocation'],
        ],
    ];

    /**
     * Recommended security headers
     *
     * @var array
     */
    protected $recommendedHeaders = [
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Embedder-Policy' => 'require-corp',
        'Cross-Origin-Resource-Policy' => 'same-origin',
        'X-XSS-Protection' => '1; mode=block',
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url') ?: config('app.url');

        if (!$url) {
            $this->error('No URL provided and APP_URL not set');
            return 1;
        }

        $this->info('🔍 Auditing Security Headers');
        $this->info("URL: {$url}");
        $this->newLine();

        try {
            // Make HTTP request without following redirects to get initial response headers
            $response = Http::timeout(10)
                ->withoutVerifying() // Allow self-signed certs in dev
                ->withOptions([
                    'allow_redirects' => false, // Don't follow redirects to test initial response
                ])
                ->get($url);

            $headers = $response->headers();
            $results = $this->analyzeHeaders($headers);

            if ($this->option('json')) {
                $this->line(json_encode($results, JSON_PRETTY_PRINT));
                return 0;
            }

            $this->displayResults($results, $url);

            // Return exit code based on critical issues
            return $results['score'] >= 70 ? 0 : 1;

        } catch (\Exception $e) {
            $this->error('Failed to fetch headers: ' . $e->getMessage());
            $this->warn('Make sure the application is running and accessible');
            return 1;
        }
    }

    /**
     * Analyze security headers
     *
     * @param  array  $headers
     * @return array
     */
    protected function analyzeHeaders(array $headers): array
    {
        $results = [
            'timestamp' => now()->toIso8601String(),
            'headers_found' => [],
            'headers_missing' => [],
            'issues' => [],
            'score' => 0,
            'max_score' => 0,
            'grade' => 'F',
        ];

        $totalScore = 0;
        $maxScore = 0;

        // Normalize header keys (case-insensitive)
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = is_array($value) ? $value[0] : $value;
        }

        // Check required headers
        foreach ($this->requiredHeaders as $header => $config) {
            $headerKey = strtolower($header);
            $maxScore += $this->getHeaderScore($config['severity']);

            if (isset($normalizedHeaders[$headerKey])) {
                $value = $normalizedHeaders[$headerKey];
                $results['headers_found'][$header] = $value;

                // Validate header value
                $validation = $this->validateHeader($header, $value, $config);

                if ($validation['valid']) {
                    $totalScore += $this->getHeaderScore($config['severity']);
                    $results['issues'][] = [
                        'header' => $header,
                        'severity' => 'INFO',
                        'message' => "✅ {$header} properly configured",
                        'value' => $value,
                    ];
                } else {
                    $results['issues'][] = [
                        'header' => $header,
                        'severity' => 'WARNING',
                        'message' => $validation['message'],
                        'value' => $value,
                    ];
                    $totalScore += $this->getHeaderScore($config['severity']) * 0.5; // Partial credit
                }
            } else {
                $results['headers_missing'][] = $header;
                $results['issues'][] = [
                    'header' => $header,
                    'severity' => $config['severity'],
                    'message' => "❌ {$header} is missing",
                    'recommendation' => $config['description'],
                ];
            }
        }

        // Check recommended headers
        foreach ($this->recommendedHeaders as $header => $expectedValue) {
            $headerKey = strtolower($header);

            if (isset($normalizedHeaders[$headerKey])) {
                $results['headers_found'][$header] = $normalizedHeaders[$headerKey];
            } else {
                $results['issues'][] = [
                    'header' => $header,
                    'severity' => 'LOW',
                    'message' => "ℹ️  {$header} is missing (recommended)",
                ];
            }
        }

        // Calculate score and grade
        $results['score'] = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;
        $results['max_score'] = $maxScore;
        $results['grade'] = $this->calculateGrade($results['score']);

        return $results;
    }

    /**
     * Validate header value
     *
     * @param  string  $header
     * @param  string  $value
     * @param  array  $config
     * @return array
     */
    protected function validateHeader(string $header, string $value, array $config): array
    {
        $checks = $config['checks'] ?? [];
        $issues = [];

        foreach ($checks as $check) {
            if (stripos($value, $check) === false) {
                $issues[] = "Missing '{$check}'";
            }
        }

        if (!empty($issues)) {
            return [
                'valid' => false,
                'message' => "⚠️  {$header} may be misconfigured: " . implode(', ', $issues),
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get score for header severity
     *
     * @param  string  $severity
     * @return int
     */
    protected function getHeaderScore(string $severity): int
    {
        return match($severity) {
            'CRITICAL' => 30,
            'HIGH' => 20,
            'MEDIUM' => 10,
            'LOW' => 5,
            default => 0,
        };
    }

    /**
     * Calculate grade from score
     *
     * @param  int  $score
     * @return string
     */
    protected function calculateGrade(int $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * Display results
     *
     * @param  array  $results
     * @param  string  $url
     * @return void
     */
    protected function displayResults(array $results, string $url): void
    {
        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('SECURITY HEADERS AUDIT RESULTS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Score and Grade
        $grade = $results['grade'];
        $score = $results['score'];

        $gradeColor = match($grade) {
            'A' => 'info',
            'B' => 'info',
            'C' => 'warn',
            'D' => 'warn',
            default => 'error',
        };

        $this->$gradeColor("Grade: {$grade} ({$score}/100)");
        $this->newLine();

        // Headers Found
        if (!empty($results['headers_found'])) {
            $this->info('✅ Headers Found: ' . count($results['headers_found']));
            if ($this->option('detailed')) {
                foreach ($results['headers_found'] as $header => $value) {
                    $truncated = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
                    $this->line("   {$header}: {$truncated}");
                }
                $this->newLine();
            }
        }

        // Headers Missing
        if (!empty($results['headers_missing'])) {
            $this->error('❌ Critical Headers Missing: ' . count($results['headers_missing']));
            foreach ($results['headers_missing'] as $header) {
                $this->line("   - {$header}");
            }
            $this->newLine();
        }

        // Issues by Severity
        $critical = array_filter($results['issues'], fn($i) => $i['severity'] === 'CRITICAL');
        $high = array_filter($results['issues'], fn($i) => $i['severity'] === 'HIGH');
        $medium = array_filter($results['issues'], fn($i) => $i['severity'] === 'MEDIUM');
        $warnings = array_filter($results['issues'], fn($i) => $i['severity'] === 'WARNING');

        if ($critical) {
            $this->error('🔴 CRITICAL Issues: ' . count($critical));
            foreach ($critical as $issue) {
                $this->line("   {$issue['message']}");
                if (isset($issue['recommendation'])) {
                    $this->line("   💡 {$issue['recommendation']}");
                }
            }
            $this->newLine();
        }

        if ($high) {
            $this->warn('🟠 HIGH Priority Issues: ' . count($high));
            foreach ($high as $issue) {
                $this->line("   {$issue['message']}");
            }
            $this->newLine();
        }

        if ($this->option('detailed')) {
            if ($warnings) {
                $this->warn('⚠️  Warnings: ' . count($warnings));
                foreach ($warnings as $issue) {
                    $this->line("   {$issue['message']}");
                }
                $this->newLine();
            }
        }

        // Recommendations
        if ($score < 70) {
            $this->newLine();
            $this->warn('📋 Recommendations:');
            $this->line('1. Ensure EnhancedSecurityHeaders middleware is enabled');
            $this->line('2. Configure HTTPS for HSTS to work');
            $this->line('3. Review CSP configuration for your application needs');
            $this->line('4. Test headers with: curl -I ' . $url);
        } else {
            $this->newLine();
            $this->info('✅ Security headers are properly configured!');
        }

        // Quick Test Command
        $this->newLine();
        $this->info('Quick Test:');
        $this->line("curl -I {$url} | grep -i 'content-security-policy\\|strict-transport\\|x-frame'");
    }
}
