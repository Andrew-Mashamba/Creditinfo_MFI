<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Loan Disbursement Confirmation - MFI Management System</title>
    <style nonce="{{ csp_nonce() }}">
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 750px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        .header img {
            max-width: 180px;
            height: auto;
            position: relative;
            z-index: 1;
        }
        .header h1 {
            color: white;
            margin: 15px 0 0 0;
            font-size: 28px;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }
        .content {
            padding: 40px;
            background-color: #ffffff;
        }
        .success-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .loan-details {
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        .loan-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .loan-info-item {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .loan-info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .loan-info-label {
            font-weight: 600;
            color: #003366;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .loan-info-value {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-top: 5px;
        }
        .repayment-schedule {
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .schedule-table th,
        .schedule-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .schedule-table th {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        .schedule-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .schedule-table tr:hover {
            background-color: #e3f2fd;
        }
        .payment-section {
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border-radius: 12px;
            border: 2px solid #28a745;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        .payment-method {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .payment-method h4 {
            color: #003366;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }
        .payment-method ol {
            margin: 0;
            padding-left: 20px;
        }
        .payment-method li {
            margin: 8px 0;
            color: #555;
            line-height: 1.5;
        }
        .control-number-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #003366;
        }
        .control-number {
            font-weight: bold;
            color: #003366;
            font-size: 16px;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
            font-size: 18px;
        }
        .online-payment {
            text-align: center;
            margin: 30px 0;
            padding: 30px;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            border-radius: 12px;
            color: white;
        }
        .online-payment h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 300;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        .footer {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .contact-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            border: 1px solid #dee2e6;
        }
        .contact-info h4 {
            color: #003366;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        .contact-icon {
            font-size: 20px;
            margin-right: 10px;
            color: #003366;
        }
        .important-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .important-notice h4 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }
        .important-notice ul {
            margin: 0;
            padding-left: 20px;
        }
        .important-notice li {
            margin: 8px 0;
            color: #856404;
        }
        .section-title {
            color: #003366;
            font-size: 22px;
            font-weight: 600;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #003366;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/nbc.png') }}" alt="NBC Bank Logo">
            <h1>Loan Disbursement Confirmation</h1>
        </div>
        
        <div class="content">
            <div class="success-badge">✅ Loan Successfully Disbursed</div>
            
            <h2 style="color: #003366; margin-bottom: 20px;">Congratulations, {{ $memberName }}!</h2>
            
            <p style="font-size: 16px; line-height: 1.7; color: #555;">
                Your loan has been successfully disbursed and the funds are now available in your account. 
                Please review the details below and ensure timely repayment to maintain a good credit record.
            </p>
            
            <div class="loan-details">
                <h3 class="section-title">📋 Loan Details</h3>
                <div class="loan-info">
                    <div class="loan-info-item">
                        <div class="loan-info-label">Loan Amount</div>
                        <div class="loan-info-value">TZS {{ number_format($loanDetails['approved_amount'] ?? 0, 2) }}</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">Loan Term</div>
                        <div class="loan-info-value">{{ $loanDetails['tenure'] ?? 0 }} months</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">Interest Rate</div>
                        <div class="loan-info-value">{{ $loanDetails['interest_rate'] ?? 0 }}% per annum</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">Monthly Installment</div>
                        <div class="loan-info-value">TZS {{ number_format($loanDetails['monthly_installment'] ?? 0, 2) }}</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">Disbursement Date</div>
                        <div class="loan-info-value">{{ $loanDetails['disbursement_date'] ?? date('d/m/Y') }}</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">First Payment Due</div>
                        <div class="loan-info-value">{{ $loanDetails['first_payment_date'] ?? date('d/m/Y', strtotime('+1 month')) }}</div>
                    </div>
                </div>
            </div>

            @if(!empty($repaymentSchedule))
            <div class="repayment-schedule">
                <h3 class="section-title">📅 Repayment Schedule</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Below is your complete repayment schedule. Please ensure payments are made on or before the due dates to avoid late payment charges.
                </p>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Installment #</th>
                            <th>Due Date</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Total Amount</th>
                            <th>Remaining Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repaymentSchedule as $index => $installment)
                        <tr>
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td>{{ $installment['due_date'] ?? '' }}</td>
                            <td>TZS {{ number_format($installment['principal'] ?? 0, 2) }}</td>
                            <td>TZS {{ number_format($installment['interest'] ?? 0, 2) }}</td>
                            <td><strong>TZS {{ number_format($installment['total'] ?? 0, 2) }}</strong></td>
                            <td>TZS {{ number_format($installment['balance'] ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            
            @if(!empty($controlNumbers))
            <div class="payment-section">
                <h3 class="section-title">💳 Payment Information</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Use the control number below to make your loan repayments through any of our payment channels:
                </p>
                
                @foreach($controlNumbers as $control)
                <div class="control-number-box">
                    <p style="margin: 0 0 10px 0;"><strong>Service:</strong> {{ $control['service_name'] ?? $control['service_code'] }}</p>
                    <p style="margin: 0 0 10px 0;"><strong>Control Number:</strong> <span class="control-number">{{ $control['control_number'] }}</span></p>
                    <p style="margin: 0;"><strong>Amount:</strong> <span class="amount">TZS {{ number_format($control['amount'], 2) }}</span></p>
                </div>
                @endforeach

                <div class="payment-methods">
                    <div class="payment-method">
                        <h4>🏦 NBC Bank Branches</h4>
                        <ol>
                            <li>Visit any NBC Bank branch nationwide</li>
                            <li>Present your <span class="highlight">Control Number</span> to the teller</li>
                            <li>Pay the required amount in cash or from your account</li>
                            <li>Keep the payment receipt for your records</li>
                        </ol>
                    </div>
                    
                    <div class="payment-method">
                        <h4>📱 NBC Kiganjani (Mobile Banking)</h4>
                        <ol>
                            <li>Open NBC Kiganjani app on your phone</li>
                            <li>Select <span class="highlight">"Payments"</span> or <span class="highlight">"Bills"</span></li>
                            <li>Enter your <span class="highlight">Control Number</span></li>
                            <li>Confirm the amount and complete payment</li>
                            <li>Save the transaction receipt</li>
                        </ol>
                    </div>
                    
                    <div class="payment-method">
                        <h4>🏪 NBC Wakala (Agent Banking)</h4>
                        <ol>
                            <li>Visit any NBC Wakala agent near you</li>
                            <li>Provide your <span class="highlight">Control Number</span></li>
                            <li>Pay the amount in cash</li>
                            <li>Receive payment confirmation SMS</li>
                            <li>Keep the transaction slip</li>
                        </ol>
                    </div>
                </div>
            </div>
            @endif
            
            @if($paymentLink)
            <div class="online-payment">
                <h3>💻 Easy Online Payment</h3>
                <p style="font-size: 16px; margin-bottom: 25px;">
                    Pay your loan installments quickly and securely online using your mobile phone or computer.
                </p>
                <a href="{{ $paymentLink }}" class="button">
                    🚀 Pay Online Now
                </a>
                <p style="font-size: 14px; margin-top: 20px; opacity: 0.9;">
                    Secure • Fast • Convenient
                </p>
            </div>
            @endif

            <div class="important-notice">
                <h4>⚠️ Important Reminders</h4>
                <ul>
                    <li><strong>Timely Payments:</strong> Ensure payments are made on or before the due date to avoid late payment charges</li>
                    <li><strong>Control Number:</strong> Keep your control number safe - you'll need it for all payments</li>
                    <li><strong>Payment Receipts:</strong> Always keep payment receipts and SMS confirmations for your records</li>
                    <li><strong>Early Settlement:</strong> You can pay off your loan early without penalty</li>
                    <li><strong>Financial Difficulties:</strong> Contact us immediately if you face any financial challenges</li>
                </ul>
            </div>
            
            <div class="contact-info">
                <h4>📞 Need Assistance?</h4>
                <p style="color: #666; margin-bottom: 20px;">
                    Our dedicated support team is here to help you with any questions about your loan or payments:
                </p>
                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <strong>Phone</strong><br>
                            +255 22 219 7000
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div>
                            <strong>Email</strong><br>
                            support@nbcsaccos.co.tz
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🏦</div>
                        <div>
                            <strong>Branches</strong><br>
                            Visit any NBC branch
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">💬</div>
                        <div>
                            <strong>WhatsApp</strong><br>
                            +255 755 123 456
                        </div>
                    </div>
                </div>
            </div>
            
            <p style="font-size: 16px; line-height: 1.7; color: #555; margin-top: 30px;">
                Thank you for choosing MFI Management System as your trusted financial partner. We're committed to helping you achieve your financial goals!
            </p>
            
            <p style="margin-top: 30px; font-weight: 600; color: #003366;">
                Best regards,<br>
                <strong>MFI Management System Team</strong>
            </p>
        </div>
        
        <div class="footer">
            <p style="margin-bottom: 10px;">
                This email is confidential and intended for the recipient specified in the message only. 
                It is strictly forbidden to share any part of this message with any third party, without a written consent of the sender.
            </p>
            <p style="margin: 0;">
                © {{ date('Y') }} NBC Bank. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html> 