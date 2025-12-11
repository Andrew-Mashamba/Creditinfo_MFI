<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Notice - {{ $document->document_number }}</title>
    <style nonce="{{ csp_nonce() }}">
        @page {
            margin: 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .law-firm-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
        }
        .law-firm-header h1 {
            font-size: 16pt;
            margin: 0 0 5px 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .law-firm-header p {
            font-size: 10pt;
            margin: 2px 0;
        }
        .sacco-reference {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }
        .sacco-reference p {
            margin: 2px 0;
            font-size: 10pt;
        }
        .notice-header {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 15px;
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 20px;
            letter-spacing: 3px;
            border: 4px solid #333;
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
            margin-bottom: 25px;
            padding: 15px;
            border: 2px solid #000;
            background-color: #fff;
        }
        .recipient p {
            margin: 3px 0;
        }
        .subject {
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            text-align: center;
            font-size: 13pt;
        }
        .body-text {
            text-align: justify;
            margin-bottom: 15px;
        }
        .numbered-paragraph {
            margin-left: 30px;
            margin-bottom: 15px;
        }
        .numbered-paragraph .number {
            font-weight: bold;
            margin-right: 10px;
        }
        .amount-table {
            width: 90%;
            margin: 25px auto;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        .amount-table th, .amount-table td {
            border: 1px solid #333;
            padding: 10px 15px;
            text-align: left;
        }
        .amount-table th {
            background-color: #333;
            color: #fff;
            font-weight: bold;
            width: 60%;
        }
        .amount-table td {
            text-align: right;
            font-weight: bold;
            font-size: 11pt;
        }
        .amount-table .total-row th,
        .amount-table .total-row td {
            background-color: #000;
            color: #fff;
            font-size: 12pt;
        }
        .demand-box {
            border: 4px solid #000;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        .demand-box h3 {
            margin: 0 0 15px 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .demand-box .deadline {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
        }
        .legal-consequences {
            background-color: #f9f9f9;
            border: 2px solid #333;
            padding: 20px;
            margin: 25px 0;
        }
        .legal-consequences h4 {
            margin: 0 0 15px 0;
            font-size: 12pt;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .legal-consequences ol {
            margin: 0;
            padding-left: 25px;
        }
        .legal-consequences li {
            margin-bottom: 8px;
        }
        .court-info {
            background-color: #e6e6e6;
            border: 2px solid #333;
            padding: 15px;
            margin: 20px 0;
        }
        .court-info h4 {
            margin: 0 0 10px 0;
            text-align: center;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-line {
            margin-top: 60px;
            width: 300px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 15px;
        }
        .advocate-stamp {
            margin-top: 30px;
            border: 2px solid #333;
            padding: 15px;
            width: 200px;
            text-align: center;
            font-size: 10pt;
        }
        .cc-section {
            margin-top: 25px;
            font-size: 10pt;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .acknowledgment {
            margin-top: 40px;
            border: 2px solid #333;
            padding: 15px;
        }
        .acknowledgment h4 {
            margin: 0 0 15px 0;
            text-align: center;
        }
        .acknowledgment-line {
            margin-top: 30px;
            width: 60%;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <!-- Law Firm Header (if handled by external lawyers) -->
    <div class="law-firm-header">
        <h1>{{ $lawFirm->name ?? 'LEGAL DEPARTMENT' }}</h1>
        <p>{{ $lawFirm->address ?? ($organization->address ?? 'P.O. Box XXXX, Dar es Salaam, Tanzania') }}</p>
        <p>Advocates | Legal Consultants | Notaries Public</p>
        <p>Tel: {{ $lawFirm->phone ?? ($organization->phone ?? '+255 XXX XXX XXX') }} | Email: {{ $lawFirm->email ?? 'legal@nbcsacco.co.tz' }}</p>
    </div>

    <!-- SACCO Reference -->
    <div class="sacco-reference">
        <p><strong>Acting for and on behalf of:</strong> {{ $organization->name ?? 'NBC SACCO ORGANIZATION' }}</p>
        <p><strong>Client Reference:</strong> {{ $document->loan_id }}</p>
        <p><strong>Our File Reference:</strong> {{ $document->document_number }}</p>
    </div>

    <!-- Notice Header -->
    <div class="notice-header">
        STATUTORY NOTICE OF INTENTION TO SUE<br>
        PURSUANT TO LAW OF LIMITATION ACT CAP. 89 R.E. 2019
    </div>

    <!-- Reference Section -->
    <div class="reference-section">
        <div class="reference-left">
            <p><strong>Notice No:</strong> {{ $document->document_number }}</p>
            <p><strong>Loan Account:</strong> {{ $document->loan_id }}</p>
        </div>
        <div class="reference-right">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($document->generated_date)->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Recipient -->
    <div class="recipient">
        <p><strong>DELIVERED BY REGISTERED POST / PERSONAL SERVICE</strong></p>
        <p>&nbsp;</p>
        <p><strong>TO:</strong></p>
        <p>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}</p>
        <p>National ID/Passport: {{ $client->id_number ?? 'N/A' }}</p>
        <p>{{ $client->address ?? 'Registered Address' }}</p>
        <p>{{ $client->city ?? 'Dar es Salaam' }}, {{ $client->region ?? 'Tanzania' }}</p>
    </div>

    <!-- Subject -->
    <p class="subject">RE: STATUTORY NOTICE PRIOR TO LEGAL PROCEEDINGS FOR RECOVERY OF OUTSTANDING LOAN DEBT</p>

    <!-- Body -->
    <div class="body-text">
        <p><strong>TAKE NOTICE THAT:</strong></p>
    </div>

    <div class="numbered-paragraph">
        <p><span class="number">1.</span> We act for {{ $organization->name ?? 'NBC SACCO ORGANIZATION' }} (hereinafter "Our Client")
        in respect of monies due and owing by you under Loan Account Number {{ $document->loan_id }}.</p>
    </div>

    <div class="numbered-paragraph">
        <p><span class="number">2.</span> Our Client granted you a loan facility in the sum of
        TZS {{ number_format($loan->amount ?? 0, 2) }} vide Loan Agreement dated
        {{ $loan->disbursement_date ? \Carbon\Carbon::parse($loan->disbursement_date)->format('d F Y') : 'N/A' }},
        which you have failed, refused and/or neglected to repay as per the agreed terms and conditions.</p>
    </div>

    <div class="numbered-paragraph">
        <p><span class="number">3.</span> Despite multiple demands for payment including Warning Letter dated
        {{ isset($warningLetter) ? \Carbon\Carbon::parse($warningLetter->generated_date)->format('d F Y') : 'N/A' }},
        Demand Letter dated {{ isset($demandLetter) ? \Carbon\Carbon::parse($demandLetter->generated_date)->format('d F Y') : 'N/A' }},
        and Final Notice dated {{ isset($finalNotice) ? \Carbon\Carbon::parse($finalNotice->generated_date)->format('d F Y') : 'N/A' }},
        you have remained in default and failed to honor your contractual obligations.</p>
    </div>

    <div class="numbered-paragraph">
        <p><span class="number">4.</span> Your account has been in arrears for <strong>{{ $document->days_in_arrears }} days</strong>
        and the total amount now due, owing and payable by you to Our Client is as follows:</p>
    </div>

    <!-- Amount Table -->
    <table class="amount-table">
        <tr>
            <th>Principal Outstanding</th>
            <td>TZS {{ number_format($document->principal_outstanding, 2) }}</td>
        </tr>
        <tr>
            <th>Accrued Interest</th>
            <td>TZS {{ number_format($document->interest_outstanding, 2) }}</td>
        </tr>
        <tr>
            <th>Penalty Charges</th>
            <td>TZS {{ number_format($document->penalty_outstanding, 2) }}</td>
        </tr>
        <tr>
            <th>Legal and Administrative Costs (Estimated)</th>
            <td>TZS {{ number_format($document->outstanding_amount * 0.20, 2) }}</td>
        </tr>
        <tr class="total-row">
            <th>TOTAL SUM CLAIMED</th>
            <td>TZS {{ number_format($document->outstanding_amount * 1.20, 2) }}</td>
        </tr>
    </table>

    <!-- Demand Box -->
    <div class="demand-box">
        <h3>STATUTORY DEMAND</h3>
        <p>You are hereby given notice to pay the full amount stated above within</p>
        <p class="deadline">THIRTY (30) DAYS</p>
        <p>from the date of service of this notice, failing which legal proceedings shall be</p>
        <p>instituted against you <strong>WITHOUT FURTHER NOTICE</strong></p>
    </div>

    <div class="numbered-paragraph">
        <p><span class="number">5.</span> This Notice is issued pursuant to and in compliance with Order XXXV of the
        Civil Procedure Code, Cap. 33 R.E. 2019 and serves as the mandatory statutory notice before commencement
        of legal proceedings.</p>
    </div>

    <!-- Legal Consequences -->
    <div class="legal-consequences">
        <h4>Legal Proceedings to be Instituted</h4>
        <ol>
            <li>Our Client shall file a civil suit against you in the appropriate Court for recovery of the debt,
            together with interest, costs, and damages.</li>
            <li>All costs of litigation including advocate fees, court fees, and execution costs shall be claimed from you.</li>
            <li>Your default shall be reported to all Credit Reference Bureaus pursuant to the Banking and Financial
            Institutions (Credit Reference Bureau) Regulations, 2012.</li>
            <li>Any security/collateral pledged for the loan shall be realized through sale/auction pursuant to the
            Law of Contract Act, Cap. 345 R.E. 2019.</li>
            <li>Demand shall be made upon your guarantor(s) for immediate payment, and legal action taken against them
            if payment is not forthcoming.</li>
            <li>Court orders shall be sought for attachment and sale of your assets including but not limited to
            bank accounts, property, and other valuables.</li>
            <li>If judgment is obtained and not satisfied, execution proceedings including civil imprisonment
            may be instituted pursuant to the Civil Procedure Code.</li>
        </ol>
    </div>

    <!-- Court Information -->
    <div class="court-info">
        <h4>INTENDED COURT OF JURISDICTION</h4>
        <p><strong>Court:</strong> The Resident Magistrate's Court / High Court of Tanzania (depending on claim value)</p>
        <p><strong>Registry:</strong> {{ $client->region ?? 'Dar es Salaam' }}</p>
        <p><strong>Cause of Action:</strong> Recovery of loan debt and interest</p>
    </div>

    <div class="body-text">
        <p>
            <strong>TAKE FURTHER NOTICE</strong> that should you wish to settle this matter out of court or make
            acceptable payment arrangements, you must contact Our Client or our offices within the statutory
            period of thirty (30) days. Failure to respond shall be deemed as your admission of the debt and
            acceptance of legal proceedings against you.
        </p>

        <p>
            Payment should be made directly to Our Client's account or our trust account, details of which
            can be obtained from our office.
        </p>
    </div>

    <!-- Closing -->
    <p>Dated this {{ \Carbon\Carbon::parse($document->generated_date)->format('jS') }} day of {{ \Carbon\Carbon::parse($document->generated_date)->format('F, Y') }}</p>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-line">
            <p><strong>{{ $advocate->name ?? 'Legal Counsel' }}</strong></p>
            <p>{{ $advocate->firm ?? 'Legal Department' }}</p>
            <p>Advocates for {{ $organization->name ?? 'the Claimant' }}</p>
        </div>

        <div class="advocate-stamp">
            <p>ADVOCATE'S STAMP</p>
            <p>[OFFICIAL SEAL]</p>
        </div>
    </div>

    <!-- CC Section -->
    <div class="cc-section">
        <p><strong>SERVED UPON:</strong></p>
        <ol>
            <li>{{ $client->first_name }} {{ $client->last_name }} - Principal Debtor (by registered post/personal service)</li>
            @if(isset($guarantor))
            <li>{{ $guarantor->name ?? 'Guarantor' }} - Guarantor (by registered post)</li>
            @endif
        </ol>
        <p>&nbsp;</p>
        <p><strong>CC:</strong></p>
        <ul>
            <li>{{ $organization->name ?? 'Our Client' }} - Managing Director</li>
            <li>Court Registry (for information)</li>
            <li>Credit Reference Bureau(s)</li>
            <li>Our File</li>
        </ul>
    </div>

    <!-- Acknowledgment -->
    <div class="acknowledgment">
        <h4>ACKNOWLEDGMENT OF SERVICE</h4>
        <p>I, the undersigned, acknowledge receipt of this Statutory Notice on this ____ day of ______________, 20____</p>
        <p>&nbsp;</p>
        <p>Name: <span class="acknowledgment-line"></span></p>
        <p>Signature: <span class="acknowledgment-line"></span></p>
        <p>ID Number: <span class="acknowledgment-line"></span></p>
        <p>Date: <span class="acknowledgment-line"></span></p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>THIS IS AN OFFICIAL LEGAL DOCUMENT</strong></p>
        <p>Notice Reference: {{ $document->document_number }}</p>
        <p>This notice is issued in compliance with Tanzanian law and constitutes statutory notice prior to legal proceedings</p>
        <p>Failure to comply with this notice will result in immediate commencement of legal action</p>
    </div>
</body>
</html>
