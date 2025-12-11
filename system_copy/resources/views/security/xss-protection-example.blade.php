<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SECURITY: Always set charset first to prevent encoding attacks --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    {{-- CSRF Token for forms --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NBC SACCOS') }} - XSS Protection Example</title>

    {{-- Example of inline styles with CSP nonce --}}
    <style nonce="{{ csp_nonce() }}">
        .secure-content {
            padding: 20px;
            background: #f0f0f0;
        }

        .example-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>XSS Protection Examples</h1>

        {{-- ========================================
             CORRECT EXAMPLES - Safe Output
             ======================================== --}}

        <div class="example-box">
            <h2>1. CORRECT: Escaped Output ({{ ... }})</h2>
            <p>User input: {{ $userInput ?? 'No input' }}</p>
            <p>User name: {{ $user->name ?? 'Anonymous' }}</p>

            {{-- This is SAFE - Laravel automatically escapes {{ }} --}}
            {{-- Even if $userInput contains <script>alert('XSS')</script>
                 it will be displayed as text, not executed --}}
        </div>

        <div class="example-box">
            <h2>2. CORRECT: Safe HTML with HTMLPurifier</h2>
            {{-- When you need to allow some HTML (like rich text), use HTMLPurifier --}}
            <div class="rich-content">
                {!! clean($richContent, 'email') !!}
            </div>

            {{-- Or using the helper --}}
            <div class="rich-content">
                {!! safe_html($richContent, 'default') !!}
            </div>
        </div>

        <div class="example-box">
            <h2>3. CORRECT: Safe URLs</h2>
            {{-- Always sanitize URLs to prevent javascript: protocol XSS --}}
            <a href="{{ safe_url($userProvidedUrl) }}">Safe Link</a>

            {{-- Laravel's url() helper is also safe --}}
            <a href="{{ url($path) }}">Internal Link</a>
        </div>

        <div class="example-box">
            <h2>4. CORRECT: Safe Attributes</h2>
            {{-- User input in attributes must be escaped --}}
            <div class="user-content" data-name="{{ $user->name }}" data-id="{{ $user->id }}">
                Content
            </div>

            {{-- Using helper for extra safety --}}
            <input type="text" value="{{ safe_attribute($userInput) }}" />
        </div>

        <div class="example-box">
            <h2>5. CORRECT: Safe JSON in JavaScript</h2>
            {{-- Never trust user data in JavaScript --}}
            <script nonce="{{ csp_nonce() }}">
                // CORRECT: Using safe_json helper
                const userData = {!! safe_json($user) !!};

                // CORRECT: Using json_encode with proper flags
                const safeData = @json($data);

                console.log('User data:', userData);
            </script>
        </div>

        <div class="example-box">
            <h2>6. CORRECT: Inline Scripts with CSP Nonce</h2>
            {{-- Always use CSP nonce for inline scripts --}}
            <script nonce="{{ csp_nonce() }}">
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('Page loaded securely');

                    // Safe way to set content
                    const element = document.getElementById('safe-content');
                    element.textContent = '{{ safe_js_string($userInput) }}';
                });
            </script>
        </div>

        <div class="example-box">
            <h2>7. CORRECT: Forms with CSRF Protection</h2>
            <form method="POST" action="{{ route('example.submit') }}">
                @csrf

                <input type="text" name="name" value="{{ old('name', $user->name) }}" />
                <input type="email" name="email" value="{{ old('email', $user->email) }}" />

                <button type="submit">Submit</button>
            </form>
        </div>

        {{-- ========================================
             INCORRECT EXAMPLES - Vulnerable
             ======================================== --}}

        <div class="example-box" style="border-color: red;">
            <h2>❌ INCORRECT EXAMPLES - DO NOT USE ❌</h2>

            <h3>1. ❌ WRONG: Unescaped Output</h3>
            <pre>
&lt;!-- DANGEROUS: Never do this with user input --&gt;
{!! $userInput !!}
            </pre>
            <p class="error">This would execute any JavaScript in $userInput!</p>

            <h3>2. ❌ WRONG: Unsafe URLs</h3>
            <pre>
&lt;!-- DANGEROUS: Can allow javascript: protocol --&gt;
&lt;a href="{!! $userProvidedUrl !!}"&gt;Link&lt;/a&gt;
            </pre>
            <p class="error">User could provide: javascript:alert('XSS')</p>

            <h3>3. ❌ WRONG: innerHTML with User Data</h3>
            <pre>
&lt;script nonce="{{ csp_nonce() }}"&gt;
    // DANGEROUS: Using innerHTML with user data
    document.getElementById('content').innerHTML = '{!! $userInput !!}';
&lt;/script&gt;
            </pre>
            <p class="error">Use textContent instead, or sanitize with HTMLPurifier first!</p>

            <h3>4. ❌ WRONG: Inline Scripts without Nonce</h3>
            <pre>
&lt;!-- DANGEROUS: CSP will block this --&gt;
&lt;script&gt;
    console.log('This will be blocked by CSP');
&lt;/script&gt;
            </pre>
            <p class="error">Always use nonce="{{ csp_nonce() }}" for inline scripts!</p>

            <h3>5. ❌ WRONG: Event Handlers in HTML</h3>
            <pre>
&lt;!-- DANGEROUS: Inline event handlers are blocked by CSP --&gt;
&lt;button onclick="alert('XSS')"&gt;Click&lt;/button&gt;
            </pre>
            <p class="error">Use addEventListener in JavaScript instead!</p>
        </div>

        {{-- ========================================
             BEST PRACTICES
             ======================================== --}}

        <div class="example-box">
            <h2>✅ Best Practices Summary</h2>
            <ol>
                <li>Always use {{ $var }} for user input (auto-escaped)</li>
                <li>Only use {!! $var !!} after sanitizing with HTMLPurifier</li>
                <li>Use safe_url() helper for URLs</li>
                <li>Use safe_json() or @json() for JSON data</li>
                <li>Always add nonce="{{ csp_nonce() }}" to inline scripts</li>
                <li>Use textContent, not innerHTML, in JavaScript</li>
                <li>Validate and sanitize all user input server-side</li>
                <li>Use CSRF tokens on all forms</li>
                <li>Set proper charset meta tag</li>
                <li>Use Content Security Policy headers</li>
            </ol>
        </div>

    </div>

    {{-- External scripts don't need nonce (allowed by CSP 'self') --}}
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- But inline scripts do need nonce --}}
    <script nonce="{{ csp_nonce() }}">
        // Initialize any page-specific functionality here
        console.log('XSS Protection Example Page');
    </script>
</body>
</html>
