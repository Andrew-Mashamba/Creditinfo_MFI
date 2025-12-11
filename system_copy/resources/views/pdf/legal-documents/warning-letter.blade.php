<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warning Letter - {{ $document->document_number }}</title>
    <style nonce="{{ csp_nonce() }}">
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #366092;
            padding-bottom: 20px;
        }
        .letterhead .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .letterhead h1 {
            color: #366092;
            font-size: 18pt;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        .letterhead .address {
            font-size: 10pt;
            color: #666;
            margin: 0;
        }
        .reference-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .reference-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .reference-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .recipient {
            margin-bottom: 20px;
        }
        .recipient p {
            margin: 0;
        }
        .subject {
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
            text-align: center;
            font-size: 12pt;
        }
        .salutation {
            margin-bottom: 15px;
        }
        .body-text {
            text-align: justify;
            margin-bottom: 15px;
        }
        .amount-table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        .amount-table th, .amount-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }
        .amount-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 60%;
        }
        .amount-table td {
            text-align: right;
            font-weight: bold;
        }
        .amount-table .total-row {
            background-color: #366092;
            color: white;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-box p {
            margin: 0;
            font-weight: bold;
            color: #856404;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-line {
            margin-top: 50px;
            width: 250px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .cc-section {
            margin-top: 20px;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <!-- Letterhead -->
    <div class="letterhead">
        @if(isset($organization->logo))
            <img src="{{ public_path('images/' . $organization->logo) }}" alt="Logo" class="logo">
        @endif
        <h1>{{ $organization->name ?? 'NBC SACCO ORGANIZATION' }}</h1>
        <p class="address">
            {{ $organization->address ?? 'P.O. Box XXXX, Dar es Salaam, Tanzania' }}<br>
            Tel: {{ $organization->phone ?? '+255 XXX XXX XXX' }} | Email: {{ $organization->email ?? 'info@nbcsacco.co.tz' }}
        </p>
    </div>

    <!-- Reference Section -->
    <div class="reference-section">
        <div class="reference-left">
            <p><strong>Our Ref:</strong> {{ $document->document_number }}</p>
            <p><strong>Loan Ref:</strong> {{ $document->loan_id }}</p>
        </div>
        <div class="reference-right">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($document->generated_date)->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Recipient -->
    <div class="recipient">
        <p>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}</p>
        <p>{{ $client->address ?? 'Member Address' }}</p>
        <p>{{ $client->city ?? 'Dar es Salaam' }}, Tanzania</p>
    </div>

    <!-- Subject -->
    <p class="subject">RE: WARNING LETTER - OVERDUE LOAN REPAYMENT</p>

    <!-- Salutation -->
    <p class="salutation">Dear {{ $client->first_name ?? 'Member' }},</p>

    <!-- Body -->
    <div class="body-text">
        <p>
            We write to bring to your attention that your loan account referenced above is currently in arrears.
            Our records indicate that your loan repayment is now <strong>{{ $document->days_in_arrears }} days</strong>
            past the due date.
        </p>

        <p>
            As per the terms and conditions of your loan agreement dated
            {{ $loan->disbursement_date ? \Carbon\Carbon::parse($loan->disbursement_date)->format('d F Y') : 'N/A' }},
            you are required to make timely payments as per the agreed repayment schedule. The current outstanding
            balance on your account is as follows:
        </p>
    </div>

    <!-- Amount Table -->
    <table class="amount-table">
        <tr>
            <th>Principal Outstanding</th>
            <td>TZS {{ number_format($document->principal_outstanding, 2) }}</td>
        </tr>
        <tr>
            <th>Interest Outstanding</th>
            <td>TZS {{ number_format($document->interest_outstanding, 2) }}</td>
        </tr>
        <tr>
            <th>Penalty Charges</th>
            <td>TZS {{ number_format($document->penalty_outstanding, 2) }}</td>
        </tr>
        <tr class="total-row">
            <th>TOTAL AMOUNT DUE</th>
            <td>TZS {{ number_format($document->outstanding_amount, 2) }}</td>
        </tr>
    </table>

    <div class="body-text">
        <p>
            We kindly request you to settle the outstanding amount or contact our office within
            <strong>14 days</strong> from the date of this letter (by {{ $document->response_deadline ? \Carbon\Carbon::parse($document->response_deadline)->format('d F Y') : \Carbon\Carbon::parse($document->generated_date)->addDays(14)->format('d F Y') }})
            to discuss a suitable repayment arrangement.
        </p>
    </div>

    <!-- Warning Box -->
    <div class="warning-box">
        <p>
            Please note that failure to respond to this notice may result in further action including:
        </p>
        <ul>
            <li>Increased penalty charges on the outstanding amount</li>
            <li>Referral to our collection department</li>
            <li>Issuance of a formal demand letter</li>
            <li>Negative impact on your credit score</li>
        </ul>
    </div>

    <div class="body-text">
        <p>
            We value our relationship with you and encourage you to take immediate action to regularize your account.
            Should you have any questions or require clarification, please do not hesitate to contact our
            Loan Department at {{ $organization->phone ?? 'our office numbers' }} or visit our nearest branch.
        </p>

        <p>
            We trust this matter will receive your urgent attention.
        </p>
    </div>

    <!-- Closing -->
    <p>Yours faithfully,</p>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-line">
            <p><strong>{{ $officer->name ?? 'Loan Recovery Officer' }}</strong></p>
            <p>{{ $officer->title ?? 'Credit & Recovery Department' }}</p>
        </div>
    </div>

    <!-- CC Section -->
    <div class="cc-section">
        <p><strong>CC:</strong></p>
        <ul>
            <li>Branch Manager</li>
            <li>Loan File</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is an official document. Reference: {{ $document->document_number }}</p>
        <p>For inquiries, please contact our Customer Service Department</p>
    </div>
</body>
</html>
