<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Notice - {{ $document->document_number }}</title>
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
            border-bottom: 4px solid #8B0000;
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
        .final-warning {
            background-color: #8B0000;
            color: white;
            text-align: center;
            padding: 15px;
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 20px;
            letter-spacing: 3px;
            border: 3px solid #000;
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
            font-size: 14pt;
            color: #8B0000;
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
            border: 2px solid #333;
            padding: 10px 12px;
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
            background-color: #8B0000;
            color: white;
        }
        .countdown-box {
            background-color: #000;
            color: #fff;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
            border: 4px solid #8B0000;
        }
        .countdown-box h3 {
            margin: 0 0 10px 0;
            color: #fff;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .countdown-box .date {
            font-size: 20pt;
            font-weight: bold;
            color: #ff0000;
        }
        .countdown-box .days-left {
            font-size: 12pt;
            color: #ffcc00;
            margin-top: 5px;
        }
        .legal-action-box {
            background-color: #ffe6e6;
            border: 3px solid #8B0000;
            padding: 20px;
            margin: 20px 0;
        }
        .legal-action-box h4 {
            margin: 0 0 15px 0;
            color: #8B0000;
            font-size: 14pt;
            text-align: center;
            text-transform: uppercase;
        }
        .legal-action-box ol {
            margin: 0;
            padding-left: 25px;
        }
        .legal-action-box li {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .guarantor-notice {
            background-color: #fff3cd;
            border: 2px solid #856404;
            padding: 15px;
            margin: 20px 0;
        }
        .guarantor-notice h4 {
            margin: 0 0 10px 0;
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
            border-top: 2px solid #8B0000;
            padding-top: 15px;
        }
        .legal-notice {
            font-size: 9pt;
            color: #333;
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border: 2px solid #333;
        }
        .cc-section {
            margin-top: 20px;
            font-size: 10pt;
        }
        .bold-red {
            color: #8B0000;
            font-weight: bold;
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

    <!-- Final Warning Banner -->
    <div class="final-warning">
        FINAL NOTICE BEFORE LEGAL ACTION
    </div>

    <!-- Reference Section -->
    <div class="reference-section">
        <div class="reference-left">
            <p><strong>Our Ref:</strong> {{ $document->document_number }}</p>
            <p><strong>Loan Ref:</strong> {{ $document->loan_id }}</p>
            <p><strong>Previous Demand:</strong> {{ $previousDocument->document_number ?? 'N/A' }}</p>
        </div>
        <div class="reference-right">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($document->generated_date)->format('d F Y') }}</p>
            <p><strong>Days in Default:</strong> <span class="bold-red">{{ $document->days_in_arrears }} DAYS</span></p>
        </div>
    </div>

    <!-- Recipient -->
    <div class="recipient">
        <p><strong>BY REGISTERED POST / HAND DELIVERY</strong></p>
        <p>&nbsp;</p>
        <p>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}</p>
        <p>ID/Passport No: {{ $client->id_number ?? 'N/A' }}</p>
        <p>{{ $client->address ?? 'Member Address' }}</p>
        <p>{{ $client->city ?? 'Dar es Salaam' }}, Tanzania</p>
    </div>

    <!-- Subject -->
    <p class="subject">RE: FINAL NOTICE - LAST OPPORTUNITY TO AVOID LEGAL ACTION<br>LOAN ACCOUNT: {{ $document->loan_id }}</p>

    <!-- Salutation -->
    <p class="salutation">Dear {{ $client->first_name ?? 'Sir/Madam' }},</p>

    <!-- Body -->
    <div class="body-text">
        <p>
            <span class="bold-red">THIS IS YOUR FINAL NOTICE.</span>
        </p>

        <p>
            Reference is made to our Demand Letter dated
            {{ $previousDocument->generated_date ? \Carbon\Carbon::parse($previousDocument->generated_date)->format('d F Y') : 'N/A' }}
            (Ref: {{ $previousDocument->document_number ?? 'N/A' }}), and all previous communications regarding
            your overdue loan repayment which have remained unanswered and unaddressed.
        </p>

        <p>
            Your loan account has now been in continuous default for <span class="bold-red">{{ $document->days_in_arrears }} days</span>,
            which constitutes a fundamental breach of your loan agreement. Despite our repeated attempts to resolve
            this matter amicably, you have failed to make any payment or contact our office to discuss a resolution.
        </p>

        <p>
            <strong>THE TOTAL AMOUNT NOW DUE AND PAYABLE IS:</strong>
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
            <th>Accrued Penalties & Charges</th>
            <td>TZS {{ number_format($document->penalty_outstanding, 2) }}</td>
        </tr>
        <tr>
            <th>Estimated Legal Costs (if action proceeds)</th>
            <td>TZS {{ number_format($document->outstanding_amount * 0.15, 2) }}</td>
        </tr>
        <tr class="total-row">
            <th>TOTAL AMOUNT DUE</th>
            <td>TZS {{ number_format($document->outstanding_amount * 1.15, 2) }}</td>
        </tr>
    </table>

    <!-- Countdown Box -->
    <div class="countdown-box">
        <h3>ABSOLUTE DEADLINE FOR PAYMENT</h3>
        <p class="date">{{ $document->response_deadline ? \Carbon\Carbon::parse($document->response_deadline)->format('d F Y') : \Carbon\Carbon::parse($document->generated_date)->addDays(7)->format('d F Y') }}</p>
        <p class="days-left">YOU HAVE 7 CALENDAR DAYS FROM THIS NOTICE</p>
    </div>

    <!-- Legal Action Box -->
    <div class="legal-action-box">
        <h4>NOTICE OF INTENDED LEGAL ACTION</h4>
        <p>If payment is not received or satisfactory arrangement made by the deadline above, we SHALL proceed to:</p>
        <ol>
            <li>File a civil suit against you in the Court of competent jurisdiction for recovery of the debt plus legal costs and interest</li>
            <li>Execute any security/collateral held, including but not limited to auction/sale of pledged assets</li>
            <li>Report your default to all Credit Reference Bureaus in Tanzania</li>
            <li>Demand immediate payment from your guarantor(s) and initiate legal action against them if necessary</li>
            <li>Engage debt collection agencies and/or lawyers to pursue recovery, with all costs added to your debt</li>
            <li>Seek court orders for attachment and sale of your other assets</li>
        </ol>
    </div>

    <!-- Guarantor Notice -->
    @if(isset($guarantor))
    <div class="guarantor-notice">
        <h4>NOTICE TO GUARANTOR</h4>
        <p>A copy of this Final Notice has been sent to your guarantor, <strong>{{ $guarantor->name ?? 'the registered guarantor' }}</strong>,
        who will be held jointly and severally liable for this debt. Demand for payment from the guarantor will
        follow immediately after this deadline if the debt remains unpaid.</p>
    </div>
    @endif

    <div class="body-text">
        <p>
            <strong>This is your last opportunity to resolve this matter without court proceedings.</strong>
            We strongly advise you to pay the full amount due, or contact our office immediately to discuss
            an acceptable payment plan.
        </p>

        <p>
            For urgent response, contact the Head of Recovery at {{ $organization->phone ?? 'our office' }}.
        </p>
    </div>

    <!-- Closing -->
    <p>Yours faithfully,</p>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-line">
            <p><strong>{{ $officer->name ?? 'Managing Director' }}</strong></p>
            <p>{{ $officer->title ?? 'For and on behalf of the Organization' }}</p>
        </div>
    </div>

    <!-- Legal Notice -->
    <div class="legal-notice">
        <p><strong>IMPORTANT LEGAL NOTICE:</strong></p>
        <p>This Final Notice is issued in accordance with the laws of the United Republic of Tanzania governing
        credit facilities and debt recovery. This letter serves as formal notice required before commencement
        of legal proceedings. Your failure to respond shall be taken as an admission of the debt and acceptance
        of all terms of recovery. The organization reserves all legal rights and remedies available under law.
        Any costs incurred in the recovery process, including but not limited to legal fees, court costs,
        and collection expenses, shall be added to and recovered from you as part of the total debt.</p>
    </div>

    <!-- CC Section -->
    <div class="cc-section">
        <p><strong>CC:</strong></p>
        <ul>
            <li>Board of Directors</li>
            <li>Legal Counsel / External Lawyers</li>
            <li>Credit Reference Bureau(s)</li>
            <li>Guarantor: {{ $guarantor->name ?? 'As per loan file' }}</li>
            <li>Branch Manager</li>
            <li>Loan File</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>THIS IS AN OFFICIAL LEGAL DOCUMENT</strong></p>
        <p>Reference: {{ $document->document_number }} | Date: {{ \Carbon\Carbon::parse($document->generated_date)->format('d F Y') }}</p>
        <p>Failure to act on this notice will result in immediate legal action</p>
    </div>
</body>
</html>
