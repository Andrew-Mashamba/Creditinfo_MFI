<?php
/**
 * Blade Template Security Fixer
 *
 * Automatically fixes common security issues in blade templates:
 * - Adds CSP nonce to inline <script> tags
 * - Adds CSP nonce to inline <style> tags
 * - Adds charset meta tags to pages with <head>
 *
 * Usage: php fix-blade-security.php
 */

$viewsPath = __DIR__ . '/resources/views';
$fixed = 0;
$errors = 0;

// Files to skip (vendor, already fixed, etc.)
$skipPatterns = [
    '/vendor/',
    '/livewire-powergrid/',
    '/security/xss-protection-example.blade.php',
];

echo "🔧 Blade Template Security Fixer\n";
echo "================================\n\n";

function shouldSkipFile($filePath, $skipPatterns) {
    foreach ($skipPatterns as $pattern) {
        if (strpos($filePath, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function fixBladeFile($filePath) {
    $content = file_get_contents($filePath);
    $original = $content;
    $changes = [];

    // Fix 1: Add nonce to inline <script> tags without nonce
    // Match <script> tags that don't have src= or nonce= attributes
    $scriptPattern = '/<script(?!\s+nonce)(?![^>]*\bsrc\s*=)([^>]*)>/i';
    if (preg_match_all($scriptPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach (array_reverse($matches[0]) as $match) {
            $tag = $match[0];
            $position = $match[1];

            // Don't add nonce if it already has one
            if (strpos($tag, 'nonce') === false && strpos($tag, 'src=') === false) {
                $newTag = '<script nonce="{{ csp_nonce() }}"' . substr($tag, 7);
                $content = substr_replace($content, $newTag, $position, strlen($tag));
                $changes[] = "Added nonce to inline <script> tag";
            }
        }
    }

    // Fix 2: Add nonce to inline <style> tags without nonce
    $stylePattern = '/<style(?!\s+nonce)([^>]*)>/i';
    if (preg_match_all($stylePattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach (array_reverse($matches[0]) as $match) {
            $tag = $match[0];
            $position = $match[1];

            if (strpos($tag, 'nonce') === false) {
                $newTag = '<style nonce="{{ csp_nonce() }}"' . substr($tag, 6);
                $content = substr_replace($content, $newTag, $position, strlen($tag));
                $changes[] = "Added nonce to inline <style> tag";
            }
        }
    }

    // Fix 3: Add charset meta tag if missing
    if (preg_match('/<head>/i', $content) && !preg_match('/<meta[^>]*charset[^>]*>/i', $content)) {
        $content = preg_replace(
            '/(<head[^>]*>)/i',
            "$1\n    <meta charset=\"UTF-8\">",
            $content,
            1
        );
        $changes[] = "Added charset meta tag";
    }

    // Only write if changes were made
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        return $changes;
    }

    return [];
}

// Find all blade files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$bladeFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getFilename(), '.blade.php') !== false) {
        $bladeFiles[] = $file->getPathname();
    }
}

echo "Found " . count($bladeFiles) . " blade files\n\n";

// Process each file
foreach ($bladeFiles as $filePath) {
    $relativePath = str_replace($viewsPath . '/', '', $filePath);

    // Skip certain files
    if (shouldSkipFile($filePath, $skipPatterns)) {
        echo "⏭️  Skipping: $relativePath\n";
        continue;
    }

    try {
        $changes = fixBladeFile($filePath);

        if (!empty($changes)) {
            echo "✅ Fixed: $relativePath\n";
            foreach ($changes as $change) {
                echo "   - $change\n";
            }
            $fixed++;
        }
    } catch (Exception $e) {
        echo "❌ Error fixing $relativePath: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n================================\n";
echo "Summary:\n";
echo "Files fixed: $fixed\n";
echo "Errors: $errors\n";
echo "================================\n";
