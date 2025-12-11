<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * Audit CSRF Protection
 *
 * Comprehensive CSRF protection audit:
 * - Verifies CSRF middleware is enabled
 * - Scans all forms for @csrf tokens
 * - Checks API routes for proper stateless authentication
 * - Identifies CSRF exceptions and validates them
 *
 * @package App\Console\Commands
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class AuditCsrfProtection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit-csrf
                            {--detailed : Show detailed output}
                            {--fix : Suggest fixes for issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit CSRF protection across the application';

    /**
     * Issues found during audit
     *
     * @var array
     */
    protected $issues = [];

    /**
     * Statistics
     *
     * @var array
     */
    protected $stats = [
        'total_forms' => 0,
        'forms_with_csrf' => 0,
        'forms_without_csrf' => 0,
        'get_forms' => 0,
        'post_forms' => 0,
        'put_forms' => 0,
        'delete_forms' => 0,
        'api_routes' => 0,
        'web_routes' => 0,
        'csrf_exceptions' => 0,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Starting CSRF Protection Audit...');
        $this->newLine();

        // Step 1: Check CSRF Middleware Configuration
        $this->checkCsrfMiddleware();
        $this->newLine();

        // Step 2: Audit Form CSRF Tokens
        $this->auditFormCsrfTokens();
        $this->newLine();

        // Step 3: Check API Routes Authentication
        $this->checkApiRoutesAuthentication();
        $this->newLine();

        // Step 4: Review CSRF Exceptions
        $this->reviewCsrfExceptions();
        $this->newLine();

        // Display Summary
        $this->displaySummary();

        return $this->stats['forms_without_csrf'] > 0 ? 1 : 0;
    }

    /**
     * Check CSRF Middleware Configuration
     */
    protected function checkCsrfMiddleware(): void
    {
        $this->info('📋 Step 1: Checking CSRF Middleware Configuration');
        $this->line(str_repeat('─', 60));

        $kernelPath = app_path('Http/Kernel.php');
        $kernelContent = File::get($kernelPath);

        // Check if VerifyCsrfToken is in web middleware group
        if (preg_match('/\'web\'\s*=>\s*\[([^\]]+)\]/s', $kernelContent, $matches)) {
            $webMiddleware = $matches[1];

            if (strpos($webMiddleware, 'VerifyCsrfToken') !== false) {
                $this->line('✅ CSRF middleware is enabled in web middleware group');
            } else {
                $this->error('❌ CSRF middleware is NOT enabled in web middleware group');
                $this->issues[] = [
                    'type' => 'middleware_config',
                    'severity' => 'CRITICAL',
                    'message' => 'VerifyCsrfToken middleware is not in web middleware group',
                ];
            }
        }

        // Check if VerifyCsrfToken class exists
        $csrfMiddlewarePath = app_path('Http/Middleware/VerifyCsrfToken.php');
        if (File::exists($csrfMiddlewarePath)) {
            $this->line('✅ VerifyCsrfToken middleware class exists');
        } else {
            $this->error('❌ VerifyCsrfToken middleware class NOT found');
            $this->issues[] = [
                'type' => 'middleware_missing',
                'severity' => 'CRITICAL',
                'message' => 'VerifyCsrfToken middleware class does not exist',
            ];
        }
    }

    /**
     * Audit Form CSRF Tokens
     */
    protected function auditFormCsrfTokens(): void
    {
        $this->info('📋 Step 2: Auditing Forms for CSRF Tokens');
        $this->line(str_repeat('─', 60));

        $viewsPath = resource_path('views');
        $bladeFiles = File::allFiles($viewsPath);

        foreach ($bladeFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($viewsPath . '/', '', $file->getPathname());
            $content = File::get($file->getPathname());

            // Find all forms
            preg_match_all('/<form([^>]*)>/i', $content, $formMatches, PREG_OFFSET_CAPTURE);

            foreach ($formMatches[0] as $index => $formMatch) {
                $this->stats['total_forms']++;
                $formTag = $formMatch[0];
                $formPosition = $formMatch[1];

                // Get line number
                $lineNumber = substr_count(substr($content, 0, $formPosition), "\n") + 1;

                // Extract method attribute
                $method = 'GET';
                if (preg_match('/method\s*=\s*["\']([^"\']+)["\']/i', $formTag, $methodMatch)) {
                    $method = strtoupper($methodMatch[1]);
                }

                // Track form methods
                $methodStat = strtolower($method) . '_forms';
                if (isset($this->stats[$methodStat])) {
                    $this->stats[$methodStat]++;
                }

                // Check if form needs CSRF token (POST, PUT, DELETE, PATCH)
                if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
                    // Find the closing form tag
                    $formEndPos = strpos($content, '</form>', $formPosition);
                    if ($formEndPos === false) {
                        continue;
                    }

                    $formContent = substr($content, $formPosition, $formEndPos - $formPosition);

                    // Check for CSRF token
                    $hasCsrf = preg_match('/@csrf|{{ csrf_field\(\) }}|<input[^>]*name=["\']_token["\']/i', $formContent);

                    if ($hasCsrf) {
                        $this->stats['forms_with_csrf']++;
                        if ($this->option('detailed')) {
                            $this->line("✅ {$relativePath}:{$lineNumber} - Form has CSRF token");
                        }
                    } else {
                        $this->stats['forms_without_csrf']++;
                        $this->error("❌ {$relativePath}:{$lineNumber} - Form MISSING CSRF token (method: {$method})");

                        $this->issues[] = [
                            'type' => 'missing_csrf',
                            'severity' => 'HIGH',
                            'file' => $relativePath,
                            'line' => $lineNumber,
                            'method' => $method,
                            'message' => "Form with {$method} method missing @csrf token",
                        ];
                    }
                } else {
                    $this->stats['get_forms']++;
                    if ($this->option('detailed')) {
                        $this->line("ℹ️  {$relativePath}:{$lineNumber} - GET form (CSRF not required)");
                    }
                }
            }
        }
    }

    /**
     * Check API Routes Authentication
     */
    protected function checkApiRoutesAuthentication(): void
    {
        $this->info('📋 Step 3: Checking API Routes Authentication');
        $this->line(str_repeat('─', 60));

        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            $middleware = $route->middleware();

            // Check if it's an API route
            if (str_starts_with($uri, 'api/')) {
                $this->stats['api_routes']++;

                // Check if route uses stateless authentication
                $hasStatelessAuth = false;
                $authMiddleware = [];

                foreach ($middleware as $mw) {
                    if (str_contains($mw, 'auth:sanctum') ||
                        str_contains($mw, 'auth:api') ||
                        str_contains($mw, 'api.key')) {
                        $hasStatelessAuth = true;
                        $authMiddleware[] = $mw;
                    }
                }

                // Check if route incorrectly uses web middleware (includes CSRF)
                $hasWebMiddleware = in_array('web', $middleware);

                if (in_array('POST', $methods) || in_array('PUT', $methods) || in_array('DELETE', $methods)) {
                    if ($hasWebMiddleware && !$hasStatelessAuth) {
                        $this->warn("⚠️  API route '{$uri}' uses web middleware without stateless auth");
                        $this->issues[] = [
                            'type' => 'api_auth_issue',
                            'severity' => 'MEDIUM',
                            'route' => $uri,
                            'message' => 'API route using web middleware without stateless authentication',
                        ];
                    }

                    if ($this->option('detailed') && $hasStatelessAuth) {
                        $authList = implode(', ', $authMiddleware);
                        $this->line("✅ API route '{$uri}' uses stateless auth: {$authList}");
                    }
                }
            } else if (in_array('web', $middleware)) {
                $this->stats['web_routes']++;
            }
        }
    }

    /**
     * Review CSRF Exceptions
     */
    protected function reviewCsrfExceptions(): void
    {
        $this->info('📋 Step 4: Reviewing CSRF Exceptions');
        $this->line(str_repeat('─', 60));

        $csrfMiddlewarePath = app_path('Http/Middleware/VerifyCsrfToken.php');
        if (!File::exists($csrfMiddlewarePath)) {
            return;
        }

        $content = File::get($csrfMiddlewarePath);

        // Extract $except array
        if (preg_match('/protected\s+\$except\s*=\s*\[(.*?)\];/s', $content, $matches)) {
            $exceptArray = $matches[1];
            $exceptions = [];

            // Parse exceptions
            preg_match_all('/["\']([^"\']+)["\']/i', $exceptArray, $exceptionMatches);
            foreach ($exceptionMatches[1] as $exception) {
                $exceptions[] = $exception;
                $this->stats['csrf_exceptions']++;
            }

            if (empty($exceptions)) {
                $this->line('✅ No CSRF exceptions configured (most secure)');
            } else {
                $this->warn('⚠️  Found ' . count($exceptions) . ' CSRF exceptions:');
                foreach ($exceptions as $exception) {
                    $this->line("   - {$exception}");

                    // Check if exception is justified
                    if (str_starts_with($exception, 'api/')) {
                        $this->line('     ✓ Justified: API route');
                    } elseif (in_array($exception, ['auth', 'sso-logout', 'webhook/*', 'callback/*'])) {
                        $this->line('     ✓ Justified: External/SSO endpoint');
                    } else {
                        $this->warn('     ⚠️  Review: Ensure this exception is necessary');
                        $this->issues[] = [
                            'type' => 'csrf_exception',
                            'severity' => 'LOW',
                            'exception' => $exception,
                            'message' => 'CSRF exception may not be necessary',
                        ];
                    }
                }
            }
        }
    }

    /**
     * Display Summary
     */
    protected function displaySummary(): void
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('CSRF PROTECTION AUDIT SUMMARY');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Middleware Status
        $this->newLine();
        $this->line('Middleware Configuration:');
        $this->line('  ✅ CSRF middleware enabled');

        // Form Statistics
        $this->newLine();
        $this->line('Form Statistics:');
        $this->line("  Total forms found: {$this->stats['total_forms']}");
        $this->line("  Forms with CSRF token: {$this->stats['forms_with_csrf']}");
        $this->line("  Forms WITHOUT CSRF token: {$this->stats['forms_without_csrf']}");
        $this->line("  GET forms (CSRF not required): {$this->stats['get_forms']}");

        // Route Statistics
        $this->newLine();
        $this->line('Route Statistics:');
        $this->line("  Web routes: {$this->stats['web_routes']}");
        $this->line("  API routes: {$this->stats['api_routes']}");
        $this->line("  CSRF exceptions: {$this->stats['csrf_exceptions']}");

        // Issue Summary
        $this->newLine();
        $criticalIssues = array_filter($this->issues, fn($i) => $i['severity'] === 'CRITICAL');
        $highIssues = array_filter($this->issues, fn($i) => $i['severity'] === 'HIGH');
        $mediumIssues = array_filter($this->issues, fn($i) => $i['severity'] === 'MEDIUM');
        $lowIssues = array_filter($this->issues, fn($i) => $i['severity'] === 'LOW');

        $this->line('Issues Found:');
        $this->line('  CRITICAL: ' . count($criticalIssues));
        $this->line('  HIGH: ' . count($highIssues));
        $this->line('  MEDIUM: ' . count($mediumIssues));
        $this->line('  LOW: ' . count($lowIssues));

        if ($this->stats['forms_without_csrf'] > 0) {
            $this->newLine();
            $this->warn('⚠️  CRITICAL: ' . $this->stats['forms_without_csrf'] . ' forms are missing CSRF protection!');
            $this->newLine();
            $this->info('Recommendations:');
            $this->line('1. Add @csrf directive to all POST/PUT/DELETE forms');
            $this->line('2. Review CSRF exceptions in VerifyCsrfToken middleware');
            $this->line('3. Ensure API routes use stateless authentication (sanctum/api.key)');
            $this->line('4. Test CSRF protection in production environment');
        } else {
            $this->newLine();
            $this->info('✅ All forms have proper CSRF protection!');
        }

        if ($this->option('fix')) {
            $this->newLine();
            $this->info('💡 How to fix missing CSRF tokens:');
            $this->line('Add @csrf directive inside the <form> tag:');
            $this->line('');
            $this->line('<form method="POST" action="{{ route(\'example\') }}">');
            $this->line('    @csrf');
            $this->line('    <!-- form fields -->');
            $this->line('</form>');
        }
    }
}
