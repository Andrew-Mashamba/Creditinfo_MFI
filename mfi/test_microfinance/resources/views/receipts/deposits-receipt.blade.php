<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposits Receipt</title>
    <style nonce="{{ csp_nonce() }}">
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none; border: 1px solid #000; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 10px;
            background-color: #f5f5f5;
        }
        
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 10px;
        }
        
        .receipt-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .receipt-number {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 3px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 80px;
        }
        
        .info-value {
            text-align: right;
            flex: 1;
        }
        
        .amount-section {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 10px 0;
            margin: 15px 0;
        }
        
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .amount-label {
            font-weight: bold;
            font-size: 14px;
        }
        
        .amount-value {
            font-weight: bold;
            font-size: 14px;
            text-align: right;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 10px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .close-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .close-button:hover {
            background: #545b62;
        }
        
        .barcode {
            text-align: center;
            margin: 10px 0;
            font-family: 'Libre Barcode 39', monospace;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Receipt
    </button>
    
    <button class="close-button no-print" onclick="window.close()">
        <i class="fas fa-times"></i> Close
    </button>
    
    <div class="receipt">
        <div class="header">
            <h1>MFI CORE SYSTEM</h1>
            <p>Deposits Receipt</p>
            <p>{{ $receiptData['branch'] ?? 'Main Branch' }}</p>
        </div>
        
        <div class="receipt-title">DEPOSITS RECEIPT</div>
        
        <div class="receipt-number">
            Receipt No: {{ $receiptData['receipt_number'] }}
        </div>
        
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ $receiptData['transaction_date'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Member:</span>
            <span class="info-value">{{ $receiptData['member_name'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Member No:</span>
            <span class="info-value">{{ $receiptData['member_number'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Account:</span>
            <span class="info-value">{{ $receiptData['account_number'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Account Name:</span>
            <span class="info-value">{{ $receiptData['account_name'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Depositor:</span>
            <span class="info-value">{{ $receiptData['depositor_name'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Payment Method:</span>
            <span class="info-value">{{ $receiptData['payment_method'] }}</span>
        </div>
        
        @if($receiptData['payment_method'] === 'Bank')
        <div class="info-row">
            <span class="info-label">Bank:</span>
            <span class="info-value">{{ $receiptData['bank_name'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value">{{ $receiptData['reference_number'] }}</span>
        </div>
        @endif
        
        <div class="info-row">
            <span class="info-label">Narration:</span>
            <span class="info-value">{{ $receiptData['narration'] }}</span>
        </div>
        
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">AMOUNT DEPOSITED:</span>
                <span class="amount-value">{{ $receiptData['currency'] }} {{ $receiptData['amount'] }}</span>
            </div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Balance After:</span>
            <span class="info-value">{{ $receiptData['currency'] }} {{ $receiptData['balance_after'] }}</span>
        </div>
        
        <div class="barcode">
            *{{ $receiptData['receipt_number'] }}*
        </div>
        
        <div class="signature-line">
            Processed by: {{ $receiptData['processed_by'] }}
        </div>
        
        <div class="footer">
            <p>Thank you for your deposit!</p>
            <p>This is a computer generated receipt</p>
            <p>For queries, contact your branch office</p>
            <p>Generated on: {{ $receiptData['transaction_date'] }}</p>
        </div>
    </div>
    
    <script nonce="{{ csp_nonce() }}">
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
