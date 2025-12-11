<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Loan Disbursement Notification - NBC SACCOS</title>
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
        .notification-badge {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
        .guarantor-responsibilities {
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 12px;
            border: 2px solid #ffc107;
        }
        .responsibility-item {
            background-color: #ffffff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .responsibility-item h5 {
            color: #856404;
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
        }
        .responsibility-item p {
            margin: 0;
            color: #856404;
            line-height: 1.5;
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
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .important-notice h4 {
            color: #721c24;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }
        .important-notice p {
            color: #721c24;
            margin: 0;
            line-height: 1.6;
        }
        .section-title {
            color: #003366;
            font-size: 22px;
            font-weight: 600;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #003366;
        }
        .payment-info {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #28a745;
            margin: 30px 0;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .payment-method {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .payment-method h5 {
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
            <h1>Loan Disbursement Notification</h1>
        </div>
        
        <div class="content">
            <div class="notification-badge">📋 Guarantor Notification</div>
            
            <h2 style="color: #003366; margin-bottom: 20px;">Dear {{ $guarantorName }},</h2>
            
            <p style="font-size: 16px; line-height: 1.7; color: #555;">
                This is to formally notify you that you have been listed as a guarantor for <strong>{{ $memberName }}</strong> 
                at NBC SACCOS. The loan has been successfully disbursed and is now active.
            </p>
            
            <div class="loan-details">
                <h3 class="section-title">📋 Loan Details</h3>
                <div class="loan-info">
                    <div class="loan-info-item">
                        <div class="loan-info-label">Member Name</div>
                        <div class="loan-info-value">{{ $memberName }}</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">Loan Amount</div>
                        <div class="loan-info-value">TZS {{ number_format($loanDetails['approved_amount'] ?? 0, 2) }}</div>
                    </div>
                    <div class="loan-info-item">
                        <div class="loan-info-label">Loan Term</div>
                        <div class="loan-info-value">{{ $loanDetails['tenure'] ?? 0 }} months</div>
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

            <div class="guarantor-responsibilities">
                <h3 class="section-title">⚖️ Your Responsibilities as a Guarantor</h3>
                <p style="color: #856404; margin-bottom: 20px; font-size: 16px;">
                    As a guarantor, you have important legal and financial responsibilities. Please review them carefully:
                </p>
                
                <div class="responsibility-item">
                    <h5>📅 Payment Monitoring</h5>
                    <p>Monitor the member's payment behavior and ensure they make timely loan repayments according to the schedule.</p>
                </div>
                
                <div class="responsibility-item">
                    <h5>💰 Financial Liability</h5>
                    <p>You are legally responsible for any outstanding loan amount if the member defaults on their payments.</p>
                </div>
                
                <div class="responsibility-item">
                    <h5>📞 Communication</h5>
                    <p>Maintain open communication with the member and contact us immediately if you have concerns about their ability to repay.</p>
                </div>
                
                <div class="responsibility-item">
                    <h5>📋 Documentation</h5>
                    <p>Keep your contact information updated with us and maintain records of any communications regarding the loan.</p>
                </div>
                
                <div class="responsibility-item">
                    <h5>🚨 Early Warning</h5>
                    <p>Notify us promptly if you become aware of any financial difficulties the member may be facing.</p>
                </div>
            </div>

            <div class="payment-info">
                <h3 class="section-title">💳 Payment Channels Available</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    The member can make payments through the following NBC channels:
                </p>
                
                <div class="payment-methods">
                    <div class="payment-method">
                        <h5>🏦 NBC Bank Branches</h5>
                        <ol>
                            <li>Visit any NBC Bank branch nationwide</li>
                            <li>Present the <span class="highlight">Control Number</span> to the teller</li>
                            <li>Pay the required amount in cash or from account</li>
                            <li>Keep the payment receipt for records</li>
                        </ol>
                    </div>
                    
                    <div class="payment-method">
                        <h5>📱 NBC Kiganjani (Mobile Banking)</h5>
                        <ol>
                            <li>Open NBC Kiganjani app on phone</li>
                            <li>Select <span class="highlight">"Payments"</span> or <span class="highlight">"Bills"</span></li>
                            <li>Enter the <span class="highlight">Control Number</span></li>
                            <li>Confirm amount and complete payment</li>
                            <li>Save the transaction receipt</li>
                        </ol>
                    </div>
                    
                    <div class="payment-method">
                        <h5>🏪 NBC Wakala (Agent Banking)</h5>
                        <ol>
                            <li>Visit any NBC Wakala agent nearby</li>
                            <li>Provide the <span class="highlight">Control Number</span></li>
                            <li>Pay the amount in cash</li>
                            <li>Receive payment confirmation SMS</li>
                            <li>Keep the transaction slip</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="important-notice">
                <h4>⚠️ Important Legal Notice</h4>
                <p>
                    <strong>Please note that as a guarantor, you are legally responsible for ensuring the loan is repaid.</strong> 
                    If the member fails to make payments, you may be required to fulfill the loan obligations. 
                    This includes any outstanding principal, interest, and associated charges.
                </p>
                <p style="margin-top: 15px;">
                    We recommend maintaining regular communication with the member to ensure they are meeting their 
                    payment obligations and to address any potential issues early.
                </p>
            </div>
            
            <div class="contact-info">
                <h4>📞 Need Assistance?</h4>
                <p style="color: #666; margin-bottom: 20px;">
                    Our dedicated support team is here to help you with any questions about your guarantor responsibilities:
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
                Thank you for your support and trust in NBC SACCOS. Your role as a guarantor is crucial in helping 
                our members achieve their financial goals while maintaining the integrity of our lending process.
            </p>
            
            <p style="margin-top: 30px; font-weight: 600; color: #003366;">
                Best regards,<br>
                <strong>NBC SACCOS Team</strong>
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