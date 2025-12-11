<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demand Letter - {{ $document->document_number }}</title>
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
            border-bottom: 3px solid #cc0000;
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
        .urgent-banner {
            background-color: #cc0000;
            color: white;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 20px;
            letter-spacing: 2px;
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
            color: #cc0000;
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
            background-color: #cc0000;
            color: white;
        }
        .deadline-box {
            background-color: #f8d7da;
            border: 3px solid #cc0000;
            padding: 15px;
            margin: 25px 0;
            text-align: center;
        }
        .deadline-box h3 {
            margin: 0 0 10px 0;
            color: #cc0000;
            font-size: 14pt;
        }
        .deadline-box .date {
            font-size: 16pt;
            font-weight: bold;
            color: #cc0000;
        }
        .consequences-box {
            background-color: #fff;
            border: 2px solid #333;
            padding: 15px;
            margin: 20px 0;
        }
        .consequences-box h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 12pt;
            text-decoration: underline;
        }
        .consequences-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .consequences-box li {
            margin-bottom: 5px;
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
        .legal-notice {
            font-size: 9pt;
            color: #666;
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
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

    <!-- Urgent Banner -->
    <div class="urgent-banner">
        FORMAL DEMAND FOR PAYMENT - URGENT ACTION REQUIRED
    </div>

    <!-- Reference Section -->
    <div class="reference-section">
        <div class="reference-left">
            <p><strong>Our Ref:</strong> {{ $document->document_number }}</p>
            <p><strong>Loan Ref:</strong> {{ $document->loan_id }}</p>
            <p><strong>Previous Ref:</strong> {{ $previousDocument->document_number ?? 'N/A' }}</p>
        </div>
        <div class="reference-right">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($document->generated_date)->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Recipient -->
    <div class="recipient">
        <p><strong>BY HAND / REGISTERED POST</strong></p>
        <p>&nbsp;</p>
        <p>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}</p>
        <p>{{ $client->address ?? 'Member Address' }}</p>
        <p>{{ $client->city ?? 'Dar es Salaam' }}, Tanzania</p>
    </div>

    <!-- Subject -->
    <p class="subject">RE: FORMAL DEMAND FOR PAYMENT - LOAN ACCOUNT {{ $document->loan_id }}</p>

    <!-- Salutation -->
    <p class="salutation">Dear {{ $client->first_name ?? 'Sir/Madam' }},</p>

    <!-- Body -->
    <div class="body-text">
        <p>
            Reference is made to our Warning Letter dated
            {{ $previousDocument->generated_date ? \Carbon\Carbon::parse($previousDocument->generated_date)->format('d F Y') : 'N/A' }}
            (Ref: {{ $previousDocument->document_number ?? 'N/A' }}) regarding your overdue loan repayment.
        </p>

        <p>
            Despite our previous communication, we regret to note that your loan account remains in default.
            As of the date of this letter, your account has been in arrears for
            <strong>{{ $document->days_in_arrears }} days</strong>, which constitutes a serious breach of your
            loan agreement terms and conditions.
        </p>

        <p>
            <strong>WE HEREBY FORMALLY DEMAND</strong> that you pay the full outstanding amount as detailed below:
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
            <th>Accrued Penalty Charges</th>
            <td>TZS {{ number_format($document->penalty_outstanding, 2) }}</td>
        </tr>
        <tr class="total-row">
            <th>TOTAL AMOUNT DEMANDED</th>
            <td>TZS {{ number_format($document->outstanding_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Deadline Box -->
    <div class="deadline-box">
        <h3>PAYMENT DEADLINE</h3>
        <p class="date">{{ $document->response_deadline ? \Carbon\Carbon::parse($document->response_deadline)->format('d F Y') : \Carbon\Carbon::parse($document->generated_date)->addDays(7)->format('d F Y') }}</p>
        <p>(7 days from the date of this letter)</p>
    </div>

    <div class="body-text">
        <p>
            You are hereby required to pay the full amount stated above, or alternatively,
            contact our office immediately to negotiate a binding repayment arrangement before the deadline stated above.
        </p>
    </div>

    <!-- Consequences Box -->
    <div class="consequences-box">
        <h4>TAKE NOTICE THAT FAILURE TO COMPLY WITH THIS DEMAND SHALL RESULT IN:</h4>
        <ul>
            <li>Immediate acceleration of the entire loan balance becoming due and payable</li>
            <li>Reporting of your default to Credit Reference Bureaus, affecting your credit rating</li>
            <li>Enforcement of security/collateral attached to this loan</li>
            <li>Contact and demand from your guarantor(s) for payment</li>
            <li>Initiation of legal proceedings against you without further notice</li>
            <li>Recovery of all legal costs and collection expenses from you</li>
        </ul>
    </div>

    <div class="body-text">
        <p>
            <strong>This is a formal demand and not a mere reminder.</strong> We urge you to treat this matter with
            the utmost urgency it deserves to avoid further escalation that may have serious financial and legal
            implications for you.
        </p>

        <p>
            For payment arrangements or inquiries, please contact our Recovery Department immediately at
            {{ $organization->phone ?? 'our office numbers' }}.
        </p>
    </div>

    <!-- Closing -->
    <p>Yours faithfully,</p>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-line">
            <p><strong>{{ $officer->name ?? 'Head of Credit Recovery' }}</strong></p>
            <p>{{ $officer->title ?? 'Credit & Recovery Department' }}</p>
        </div>
    </div>

    <!-- Legal Notice -->
    <div class="legal-notice">
        <p><strong>LEGAL NOTICE:</strong> This letter is issued pursuant to the law governing credit facilities
        in the United Republic of Tanzania. All rights to pursue legal remedies are hereby expressly reserved.
        Any attempt to dispose of, transfer, or encumber any collateral pledged as security for this loan shall
        be treated as fraud and reported to the relevant authorities.</p>
    </div>

    <!-- CC Section -->
    <div class="cc-section">
        <p><strong>CC:</strong></p>
        <ul>
            <li>Managing Director</li>
            <li>Legal Department</li>
            <li>Guarantor(s): {{ $guarantor->name ?? 'As per loan file' }}</li>
            <li>Loan File</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is an official legal document. Reference: {{ $document->document_number }}</p>
        <p>Please retain this letter for your records</p>
    </div>
</body>
</html>
