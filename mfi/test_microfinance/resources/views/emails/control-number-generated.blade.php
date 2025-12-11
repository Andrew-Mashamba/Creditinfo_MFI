<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine ?? 'Control Number Generated' }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fa;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
        }
        .control-number {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $subjectLine ?? 'Control Number Generated' }}</h2>
        </div>
        <div class="content">
            <p>{{ $bodyMessage ?? 'Your control number has been generated successfully.' }}</p>
            @if(isset($controlNumber))
                <div class="control-number">{{ $controlNumber }}</div>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated message from {{ config('app.name') }}. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
