<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bill - {{ $payable->bill_number }}</title>
    <style nonce="{{ csp_nonce() }}">
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }
        .header p {
            margin: 3px 0;
            color: #666;
        }
        .bill-info {
            margin-bottom: 30px;
        }
        .bill-info table {
            width: 100%;
        }
        .bill-info td {
            padding: 5px 0;
        }
        .vendor-details, .institution-details {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-bottom: 20px;
        }
        .vendor-details {
            margin-right: 3%;
        }
        .section-title {
            font-weight: bold;
            color: #667eea;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .bill-items {
            margin: 30px 0;
        }
        .bill-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .bill-items th {
            background-color: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        .bill-items td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .bill-items tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totals {
            margin-top: 30px;
            text-align: right;
        }
        .totals table {
            width: 300px;
            margin-left: auto;
        }
        .totals td {
            padding: 8px;
        }
        .totals .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #667eea;
            padding-top: 10px;
        }
        .payment-details {
            margin-top: 40px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .payment-details h3 {
            color: #667eea;
            margin-top: 0;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .stamp-area {
            margin-top: 40px;
            padding: 20px;
            border: 1px dashed #ccc;
            text-align: center;
            color: #999;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    @if(isset($payable->status) && $payable->status == 'paid')
        <div class="watermark">PAID</div>
    @elseif(isset($payable->status) && strtotime($payable->due_date) < time())
        <div class="watermark">OVERDUE</div>
    @endif
    
    <div class="header">
        <h1>{{ $institution->name ?? 'MFI' }}</h1>
        <h2>PAYMENT COMMITMENT</h2>
        <p>{{ $institution->address ?? '' }}</p>
        <p>Tel: {{ $institution->phone ?? '' }} | Email: {{ $institution->email ?? '' }}</p>
        <p>TIN: {{ $institution->tin ?? '' }}</p>
    </div>
    
    <div class="bill-info">
        <table style="width: 100%;">
            <tr>
                <td style="width: 70%;">
                    <strong>Reference Number:</strong> {{ $payable->bill_number }}<br>
                    <strong>Commitment Date:</strong> {{ $bill_date }}<br>
                    <strong>Payment Scheduled Date:</strong> <span style="color: #28a745; font-weight: bold;">{{ $due_date }}</span>
                </td>
                <td style="text-align: right;">
                    <span class="badge badge-{{ $payable->status == 'paid' ? 'paid' : (strtotime($payable->due_date) < time() ? 'overdue' : 'pending') }}">
                        {{ strtoupper($payable->status ?? 'COMMITTED') }}
                    </span>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="vendor-details">
        <div class="section-title">PAYMENT TO:</div>
        <strong>{{ $payable->vendor_name }}</strong><br>
        @if($payable->vendor_address)
            {{ $payable->vendor_address }}<br>
        @endif
        @if($payable->vendor_phone)
            Tel: {{ $payable->vendor_phone }}<br>
        @endif
        @if($payable->vendor_email)
            Email: {{ $payable->vendor_email }}<br>
        @endif
        @if($payable->vendor_tax_id)
            TIN/VAT: {{ $payable->vendor_tax_id }}
        @endif
    </div>
    
    <div class="institution-details">
        <div class="section-title">COMMITTED BY:</div>
        <strong>{{ $institution->name ?? 'MFI' }}</strong><br>
        {{ $institution->address ?? '' }}<br>
        Tel: {{ $institution->phone ?? '' }}<br>
        Email: {{ $institution->email ?? '' }}<br>
        TIN: {{ $institution->tin ?? '' }}
    </div>
    
    <div style="clear: both;"></div>
    
    <div class="bill-items">
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 50%;">Services/Goods Acknowledged</th>
                    <th style="width: 15%;">Quantity</th>
                    <th style="width: 15%;">Unit Price</th>
                    <th style="width: 15%; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        {{ $payable->description ?? 'Services/Goods Received and Acknowledged' }}
                        @if($payable->payable_type == 'installment')
                            <br><small style="color: #666;">
                                Installment Payment
                                @if(isset($payable->installments_paid) && isset($payable->installment_count))
                                    ({{ $payable->installments_paid }}/{{ $payable->installment_count }})
                                @endif
                            </small>
                        @elseif($payable->payable_type == 'subscription')
                            <br><small style="color: #666;">
                                {{ ucfirst($payable->recurring_frequency ?? 'Monthly') }} Subscription
                            </small>
                        @endif
                    </td>
                    <td>1</td>
                    <td>{{ number_format($payable->amount - ($payable->vat_amount ?? 0), 2) }}</td>
                    <td style="text-align: right;">{{ number_format($payable->amount - ($payable->vat_amount ?? 0), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td style="text-align: right;">{{ $payable->currency ?? 'TZS' }} {{ number_format($payable->amount - ($payable->vat_amount ?? 0), 2) }}</td>
            </tr>
            @if(isset($payable->vat_amount) && $payable->vat_amount > 0)
                <tr>
                    <td><strong>VAT (18%):</strong></td>
                    <td style="text-align: right;">{{ $payable->currency ?? 'TZS' }} {{ number_format($payable->vat_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td><strong>COMMITTED AMOUNT:</strong></td>
                <td style="text-align: right;">{{ $payable->currency ?? 'TZS' }} {{ number_format($payable->amount, 2) }}</td>
            </tr>
            @if(isset($payable->paid_amount) && $payable->paid_amount > 0)
                <tr>
                    <td><strong>Paid Amount:</strong></td>
                    <td style="text-align: right;">{{ $payable->currency ?? 'TZS' }} {{ number_format($payable->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Balance:</strong></td>
                    <td style="text-align: right;">{{ $payable->currency ?? 'TZS' }} {{ number_format($payable->balance, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>
    
    <div class="payment-details">
        <h3>Payment Commitment Details</h3>
        <p><strong>Payment Terms:</strong> {{ $payable->payment_terms ?? 30 }} days</p>
        <p><strong>Scheduled Payment Date:</strong> {{ $due_date }}</p>
        
        <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <strong style="color: #155724;">Our Commitment:</strong>
            <p style="margin: 10px 0; color: #155724;">
                We hereby confirm that we have received and verified your invoice for the services/goods provided. 
                Payment of <strong>{{ $payable->currency ?? 'TZS' }} {{ number_format($payable->amount, 2) }}</strong> 
                will be processed on or before <strong>{{ $due_date }}</strong>.
            </p>
        </div>
        
        @if(isset($payable->vendor_bank_name))
            <p><strong>Payment will be made to:</strong></p>
            <table style="margin-left: 20px;">
                <tr>
                    <td style="width: 150px;">Bank Name:</td>
                    <td>{{ $payable->vendor_bank_name }}</td>
                </tr>
                <tr>
                    <td>Account Name:</td>
                    <td>{{ $payable->vendor_name }}</td>
                </tr>
                @if(isset($payable->vendor_bank_account_number) && $payable->vendor_bank_account_number)
                    <tr>
                        <td>Account Number:</td>
                        <td>{{ $payable->vendor_bank_account_number }}</td>
                    </tr>
                @endif
                @if(isset($payable->vendor_bank_branch) && $payable->vendor_bank_branch)
                    <tr>
                        <td>Branch:</td>
                        <td>{{ $payable->vendor_bank_branch }}</td>
                    </tr>
                @endif
                @if(isset($payable->vendor_swift_code) && $payable->vendor_swift_code)
                    <tr>
                        <td>SWIFT Code:</td>
                        <td>{{ $payable->vendor_swift_code }}</td>
                    </tr>
                @endif
            </table>
        @endif
        
        <p style="margin-top: 15px;"><strong>Our Payment Reference:</strong> <strong>{{ $payable->bill_number }}</strong></p>
    </div>
    
    @if(isset($payable->notes) && $payable->notes)
        <div style="margin-top: 30px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
            <strong>Notes:</strong><br>
            {{ $payable->notes }}
        </div>
    @endif
    
    <div class="stamp-area">
        <p>Authorized Signature & Stamp</p>
        <div style="height: 60px;"></div>
        <p>_____________________________</p>
        <p>Date: _____________________</p>
    </div>
    
    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>This is a computer-generated document. No signature is required if electronically transmitted.</p>
        <p>Generated on: {{ $generated_at }}</p>
        <p>© {{ date('Y') }} {{ $institution->name ?? 'MFI' }}. All rights reserved.</p>
    </div>
</body>
</html>