<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 3px solid #007bff;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0;
        }
        .invoice-details {
            margin: 20px 0;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 5px 10px;
        }
        .invoice-details .label {
            font-weight: bold;
            text-align: right;
            width: 150px;
        }
        .addresses {
            margin: 30px 0;
        }
        .addresses table {
            width: 100%;
        }
        .addresses td {
            vertical-align: top;
            width: 50%;
            padding: 10px;
        }
        .address-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .address-block h3 {
            margin-top: 0;
            color: #007bff;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .totals {
            margin: 30px 0;
        }
        .totals table {
            width: 300px;
            float: right;
        }
        .totals td {
            padding: 8px;
        }
        .totals .label {
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #007bff;
        }
        .payment-info {
            clear: both;
            margin-top: 50px;
            padding: 20px;
            background: #e8f4f8;
            border-radius: 5px;
        }
        .payment-info h3 {
            color: #007bff;
            margin-top: 0;
        }
        .payment-link {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
        }
        .status-pending {
            background: #ffc107;
            color: #000;
        }
        .status-paid {
            background: #28a745;
            color: white;
        }
        .status-overdue {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    @if(isset($institution->logo_url) && $institution->logo_url)
                        <img src="{{ public_path($institution->logo_url) }}" class="logo" alt="Logo">
                    @else
                        <h2>{{ $institution->name ?? 'SACCOS' }}</h2>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="status-badge status-{{ strtolower($invoice->status) }}">
                        {{ strtoupper($invoice->status) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td class="label">Invoice Number:</td>
                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                <td class="label">Invoice Date:</td>
                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
            </tr>
            @if(isset($invoice->control_number) && $invoice->control_number)
            <tr>
                <td class="label">Control Number:</td>
                <td><strong style="color: #dc3545;">{{ $invoice->control_number }}</strong></td>
                <td class="label">Due Date:</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Payment Terms:</td>
                <td>{{ $invoice->payment_terms ?? 30 }} days</td>
                <td></td>
                <td></td>
            </tr>
            @else
            <tr>
                <td class="label">Due Date:</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                <td class="label">Payment Terms:</td>
                <td>{{ $invoice->payment_terms ?? 30 }} days</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="addresses">
        <table>
            <tr>
                <td>
                    <div class="address-block">
                        <h3>From:</h3>
                        <strong>{{ $institution->name ?? 'SACCOS Organization' }}</strong><br>
                        {{ $institution->address ?? '' }}<br>
                        @if(isset($institution->contact_phone) && $institution->contact_phone)
                            Phone: {{ $institution->contact_phone }}<br>
                        @endif
                        @if(isset($institution->email) && $institution->email)
                            Email: {{ $institution->email }}<br>
                        @endif
                        @if(isset($institution->tin_number) && $institution->tin_number)
                            TIN: {{ $institution->tin_number }}<br>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="address-block">
                        <h3>Bill To:</h3>
                        <strong>{{ $invoice->customer_name }}</strong><br>
                        @if($invoice->customer_company)
                            {{ $invoice->customer_company }}<br>
                        @endif
                        @if($invoice->customer_address)
                            {{ $invoice->customer_address }}<br>
                        @endif
                        @if($invoice->customer_phone)
                            Phone: {{ $invoice->customer_phone }}<br>
                        @endif
                        @if($invoice->customer_email)
                            Email: {{ $invoice->customer_email }}<br>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%; text-align: right;">Quantity</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $invoice->description ?: 'Services Rendered' }}
                    @if($invoice->reference_number)
                        <br><small>Ref: {{ $invoice->reference_number }}</small>
                    @endif
                </td>
                <td style="text-align: right;">1</td>
                <td style="text-align: right;">{{ number_format($invoice->amount, 2) }}</td>
                <td style="text-align: right;">{{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotal:</td>
                <td style="text-align: right;">{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->vat_amount > 0)
            <tr>
                <td class="label">VAT (18%):</td>
                <td style="text-align: right;">{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->vat_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">Total Due:</td>
                <td style="text-align: right;">{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->amount + ($invoice->vat_amount ?? 0), 2) }}</td>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr>
                <td class="label">Paid Amount:</td>
                <td style="text-align: right;">{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Balance Due:</td>
                <td style="text-align: right;">{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->balance, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if(isset($paymentUrl) && $paymentUrl && $invoice->status != 'paid')
    <div class="payment-info">
        <h3>Payment Information</h3>
        <p>To pay this invoice online, please use the following secure payment link:</p>
        <p><strong>Payment Link:</strong> {{ $paymentUrl }}</p>
        <p><small>This link will expire in 7 days. Please ensure payment is made before the due date to avoid any late fees.</small></p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>
            {{ $institution->name ?? 'SACCOS Organization' }}<br>
            @if(isset($institution->website) && $institution->website)
                {{ $institution->website }}<br>
            @endif
            Generated on {{ now()->format('d M Y H:i') }}
        </p>
    </div>
</body>
</html>