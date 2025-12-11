{{-- NBC Staff Loan Agreement Template --}}
<div class="loan-agreement-container" style="font-family: 'Times New Roman', serif; line-height: 1.6; color: #000; max-width: 800px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px;">
        <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">NBC Staff Loan Agreement</h1>
        <p style="font-size: 14px; margin: 0;">v 17 June 2025</p>
    </div>

    <!-- Agreement Introduction -->
    <div style="margin-bottom: 30px;">
        <p style="text-align: justify; margin-bottom: 15px;">
            This Agreement is made this <strong>{{ date('j') }}</strong> day of <strong>{{ date('F') }}</strong> 20<strong>{{ date('y') }}</strong> between National Bank of Commerce Limited, Registration Number 32700, a limited liability company incorporated under the Laws of Tanzania whose registered address is Sokoine Drive/Azikiwe Street, P.O. Box 1863, Dar es Salaam (hereinafter "the Bank") and <strong>{{ Auth::user()->name ?? '________________' }}</strong> of P. O. Box <strong>{{ Auth::user()->address ?? '________________' }}</strong> (hereinafter "the Staff").
        </p>
        
        <p style="text-align: justify; margin-bottom: 15px;">
            WHEREAS the purpose of the Retail Credit Policy ("the Policy") is to provide personal loans to staff on terms and conditions prescribed by the said Policy and subject to other legal and regulatory framework;
        </p>
        
        <p style="text-align: justify; margin-bottom: 15px;">
            NOW THEREFORE, this Agreement witnesses as hereunder:
        </p>
    </div>

    <!-- Section 1: THE LOAN AGREEMENT -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">1. THE LOAN AGREEMENT</h2>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>1.1.</strong> The Bank hereby agrees to grant a Personal Loan (Loan) to the Staff; on the terms and conditions set out in this Agreement and in the Retail Credit Policy version no. 6 of 2025 as amended from time to time (collectively referred to as the Agreement).
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>1.2.</strong> The Staff acknowledges and hereby agrees that the Loan will be granted upon signing and complying with all terms and conditions under this Agreement and the Retail Credit Policy version no. 6 of 2025 as amended from time to time governing staff loan.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>1.3.</strong> The staff authorizes the Bank to make any enquiries that it deems necessary in conjunction with this application including credit references.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>1.4.</strong> The Staff agrees and undertakes to pay the outstanding loan amount in full on an event of termination, resignation, retirement, retrenchment or any other form of exit from employment unless the staff is allowed to enter into a separate agreement with the Bank on repayment and including provision of adequate security for the loan.
        </p>
    </div>

    <!-- Section 2: PURPOSE -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">2. PURPOSE</h2>
        <p style="text-align: justify;">
            The purpose of the loan is <strong>{{ $loanPurpose ?? '________________________________________________________________' }}</strong>
        </p>
    </div>

    <!-- Section 3: LOAN AMOUNT AND REPAYMENT -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">3. LOAN AMOUNT AND REPAYMENT</h2>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>3.1.</strong> The Loan amount granted to the Staff under the terms of this Agreement is Tanzania Shillings <strong>{{ number_format((float)($loanAmount ?? 0), 0) }}</strong> (________________________________________________).
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>3.2.</strong> The Loan amount shall be repaid in <strong>{{ $repaymentPeriod ?? '___' }}</strong> equal monthly installments of Tanzania Shillings <strong>{{ number_format((float)($monthlyInstallment ?? 0), 0) }}</strong> (________________________________________________) or any further or lesser sum as the Bank may determine in accordance with this Agreement and shall include interest thereon at the rate provided for in this Agreement.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>3.3.</strong> The said instalments shall automatically be deducted from the Staff's NBC salary Account. The Staff may request information of the amount deducted for the repayment of the loan.
        </p>
    </div>

    <!-- Section 4: INTEREST RATE -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">4. INTEREST RATE</h2>
        <p style="text-align: justify;">
            <strong>4.1.</strong> Interest rate shall be six percent (6%) per annum on the outstanding balance of the personal loan. Changes from interest rates shall be communicated to staff prior to its implementation.
        </p>
    </div>

    <!-- Section 5: EARLY REPAYMENT -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">5. EARLY REPAYMENT</h2>
        <p style="text-align: justify;">
            <strong>5.1.</strong> The Staff shall be entitled to repay the outstanding balance of the Loan, including all accrued interest thereon in one amount or in tranches provided that the Bank receives thirty (30) days prior written notice.
        </p>
    </div>

    <!-- Section 6: SECURITY -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">6. SECURITY</h2>
        <p style="text-align: justify;">
            Subject to written agreement, the Staff hereby pledges in favour of the Bank his/her terminal benefits should the aforementioned benefits not be sufficient to cover the outstanding balance the Bank shall be entitled to recover the amount owing from any source available.
        </p>
    </div>

    <!-- Section 7: RECEIVING YOUR LOAN -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">7. RECEIVING YOUR LOAN</h2>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>7.1.</strong> The Bank will pay your loan (or the balance after we have paid off Staff debt with us or to another credit provider).
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>7.2.</strong> Once we have completed our assessment of your ability to repay your loan and checked that the amount you are eligible is the amount you have applied for or lower amount as we determine following such assessment the said amount will be deposited into your Staff account with us.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>7.3.</strong> If the Staff requires the Bank to pay off a debt to another credit provider as a condition of the loan, Staff must obtain a statement for us from the credit provider showing how much is required to pay off the debt to the credit provider and pay the remainder of the loan to the Staff (if any). You agree to use your loan for the purpose stated under clause 2.
        </p>
    </div>

    <!-- Section 8: INSTALLMENTS -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">8. INSTALLMENTS</h2>
        <p style="text-align: justify;">
            <strong>8.1.</strong> You must pay the installments to repay your loan. If we agree in writing, you can skip installment. We will adjust the amount of the remaining installment to pay for the skipped installment with interest.
        </p>
    </div>

    <!-- Section 9: EVENTS OF DEFAULT -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">9. EVENTS OF DEFAULT</h2>
        <p style="text-align: justify;">
            Termination of employment or death shall each constitute an event of default whereupon, the Bank shall be entitled by notice to the Staff or his/her legal representatives to cancel the loan and demand immediate and full repayment of any outstanding balance.
        </p>
        <p style="text-align: justify;">
            Where the employee's contract is terminated for any reason whatsoever the outstanding loan balance will immediately become due and payable, or the loan shall be converted into a commercial loan and Staff shall enter into a separate engagement with the Bank to provide security for the loan.
        </p>
    </div>

    <!-- Section 10: SET OFF -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">10. SET OFF</h2>
        <p style="text-align: justify;">
            The Bank may set off a credit balance in any of the Staff account with us against amounts due and payable from the Staff under this Agreement. If the credit balance is in a different currency to the amounts due and payable, the Bank may convert either amount at a market rate of exchange in its usual course of business for the purpose of set off.
        </p>
    </div>

    <!-- Section 11: INSURANCE COVER -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">11. INSURANCE COVER</h2>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>11.1.</strong> a) The Staff acknowledges that it is a condition of the Loan that the Staff takes up either:
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify; margin-left: 20px;">
            Credit life insurance policy to insure against the possibility of the Staff death or permanent disability during the term of his/her loan for the full outstanding amount. The insurer and insurance policy terms must be approved by the Bank. The Bank must be named as "First Loss Payee" on the insurance policy, meaning the Bank is authorised to receive first payment of the proceeds under the insurance policies, in the event of a loss which shall be used to pay off the full amount of the Staff loan, including interest, fees and charges;
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify; margin-left: 20px;">
            OR
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify; margin-left: 20px;">
            b) The Staff consents to the Bank deducting any or all of the proceeds of the employee Group Life Cover ('GLA') and Group Personal Accidents ('GPA') to settle all outstanding debts with the Bank prior to the same being paid to my legal dependants in the event of employee death or permanent disability.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>11.2</strong> Please tick below to indicate your selection between the two options below:
        </p>
        
        <div style="margin-left: 20px; margin-bottom: 15px;">
            <p style="margin-bottom: 5px;">
                <input type="checkbox" style="margin-right: 10px;"> <strong>Option 1</strong>
            </p>
            <p style="margin-bottom: 10px; margin-left: 25px;">
                The Staff authorizes the Bank, to arrange the Credit Life insurance cover required for staff loan at staff's expense
            </p>
            
            <p style="margin-bottom: 5px;">
                <input type="checkbox" style="margin-right: 10px;"> <strong>Option 2</strong>
            </p>
            <p style="margin-bottom: 10px; margin-left: 25px;">
                The Staff commits his/her GLA and GPA to be used to settle his/her outstanding debts with the Bank in line with clause 11.2 above.
            </p>
        </div>
    </div>

    <!-- Section 12: GENERAL -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">12. GENERAL</h2>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>12.1</strong> Failure by the Bank to exercise its rights in terms of this Agreement shall not be construed as a waiver or abandonment by the Bank of any of its rights under the Agreement.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>12.2</strong> The Bank shall be entitled to treat as one debt two or more than Loans owing and payable by the Staff in terms of this Agreement. In such circumstances, the Staff will be obliged to complete and sign new Loan Agreement(s) on such terms and conditions the Bank may deem fit. Any consolidated loan in terms hereof, shall in no way affect the security granted hereunder.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>12.3</strong> This Agreement contains the entire agreement between the Bank and the Staff, and no variations of the terms hereof shall be binding on the Bank and the Staff unless reduced in writing and signed by both of them.
        </p>
    </div>

    <!-- Section 13: VALIDITY OF THE AGREEMENT -->
    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">13. VALIDITY OF THE AGREEMENT</h2>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>13.1</strong> This Agreement together with any variations made thereon shall remain valid and enforceable against the party concerned (the same to include assigns and representatives) until the full amount owing from the Staff is fully repaid.
        </p>
        
        <p style="margin-bottom: 10px; text-align: justify;">
            <strong>13.2</strong> The validity, construction, and performance of this Agreement (and any claim, dispute or matter arising under or in connection with it or enforceability) and non-contractual obligations arising out of or in connection with it shall be governed by and construed in accordance with the laws of Tanzania.
        </p>
    </div>

    <!-- Acceptance Statement -->
    <div style="margin-bottom: 30px; text-align: justify;">
        <p style="font-weight: bold;">
            By opening and reading this Agreement the Borrower accepts TO HAVE READ, UNDERSTOOD AND AGREE TO COMPLY AND BE BOUND WITH THESE TERMS AND CONDITONS and these terms and conditions shall come into effect immediately upon the Borrower "clicking the acceptance Button or clicking the acceptance Box".
        </p>
    </div>

    <!-- Footer -->
    <div style="text-align: center; border-top: 1px solid #000; padding-top: 20px; font-size: 12px;">
        <p style="margin: 0;">NBC Staff loan Agreement v 17 June 2025</p>
        <p style="margin: 5px 0 0 0;">Generated on {{ date('d/m/Y H:i:s') }} | Document ID: LA-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}</p>
    </div>
</div> 