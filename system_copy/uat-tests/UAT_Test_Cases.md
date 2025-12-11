	NBC SACCOS TEST CASES 2025							
	S/n	Module	Test Case ID	Tester	Test Case Description	Expected Result	Actual Results	CoLMents 
	1	MEMBER MANAGEMENT	MM-01	Staff	Add new member with valid details	Member added successfully, unique ID generated		
			MM-02	Staff	Add member with missing mandatory fields (Nin,mobile no)	System should display validation error		
			MM-03	Staff	Search for existing member	Member profile displayed correctly		
			MM-04	Staff	Update member details	Details updated successfully		
			MM-05	Staff	Deactivate/Exit member	Member status updated to "Inactive" confirm maker/checker controls		
			MM-06	Staff	Prevent duplicate member registration	System rejects with error “Member already exists”		
			MM-07	Staff	 Edit member details-Update phone/ email/acc no/personal information	System saves updated details		
								
	2	LOAN MANAGEMENT	Loan application via Web Portal 	Member	A member Access NBCSaccos Portal	Member should be able to log into portal using the availed correct credentials		
				Member	Validate the log in credentials: Correct user name and wrong password	Do not allow user to log in error: incorrect password		
				Member	Validate the log in credentials: Wrong user name and Correct password	Do not allow user to log in error: incorrect username		
				Member	Validate the log in credentials: Wrong user name and wrong password	Do not allow user to log in error: incorrect username and incorrect password		
				Member	Member to select and view loan dashboard	A member should be able to see Loan catalogue and products.		
				Member	Member to select the required product  	Member successful selects the product  to display Existing loan list (if any) and applied loan (if any)		
				Member	Member to view loan calculator for eligibility and other computation	Member should be able to compute the DSR and decision		
				Member	Application with breaches should automatic move to deviation queue for manual processing	Member should be able to see breaches and attach supporting documents to proceed		
				Member	The customer must accept Terms and conditions for the provided loan Products, desired amount and Tenure	Member should be able to accepts Terms and conditions 		
				Member	Member receives OTP and submit application request to NBCSaccos for decisions.	Member should be able to receive OTP via email or mobile and submit application request to NBCSaccos		
				Loan officer	Loan officer Access Portal	Loan officer should be able to log into portal using the availed correct credentials		
				Loan officer	Loan officer to see loan applications applied via Wep Portal	Loan officer should be able to see loan applications applied via Wep Portal		
				Loan officer	Loan officer to review computed Debt Service Ration (DSR) and decision	Loan officer should be able to receives loans detail verify and recoLMend to loan coLMittee		
			"Loan application via NBCSacoss 
Portal"	Loan officer	Loan officer Access Portal	Loan officer should be able to log into portal using the availed correct credentials		
				Loan officer	Loan officer to apply loan applications  via NBCSaccoss Portal	Loan officer should be able to apply loan via NBCSaccoss Portal		
				Loan officer	Loan officer to review computed Debt Service Ration (DSR)	Loan officer should be able to compute the DSR, verify loans details  and recoLMend to loan coLMittee		
			LM-14	Loan CoLMittee	CoLMittee member Access Portal	Loan coLMittee member  should be able to log into portal using the availed correct credentials		
			LM-15	Loan CoLMittee	Loan coLMittee members to see all loan applications  applied 	Loan coLMittee member should be able to see loan applications 		
			LM-16	Loan CoLMittee	Loan coLMittee members to review and verify computed Debt Service Ration (DSR), Charges, and decision. 	Loan coLMittee member should be able to receives loans detail verify and decision.		
			LM-17	Accountant	 Accountant access Portal	 Accountan  should be able to log into portal using the availed correct credentials		
			LM-18	Accountant	Accountant see all loan applications  applied	 Accountan should be able to see loan applications applied		
			LM-19	Accountant	Accountant to review and verify computed Debt Service Ration (DSR), Charges, account entries and allocate NBCSaccos  transactional accounts and recoLMend to Board Chair	Accountant should be able to review and verify computed Debt Service Ration (DSR), Charges, account entries and allocate NBCSaccoss  transactional accounts and recoLMend to Board Chair		
			LM-20	Board Chair	Board Chairman Access Portal	Board Chairman  should be able to log into portal using the availed correct credentials		
			LM-21	Board Chair	Board Chairman to see loan applications applied via Wep Portal or application	Board Chairman should be able to see loan applications applied		
			LM-22	Board Chair	Board Chair to review all lending parameters and decision	Board Chairman should be able to receives loans detail, verify lending parameters and decision [Approve or Reject]		
			LM-23	loan officer	"All Loans Products Configuration and Charges
1. Onja
2. ChapChap
3. Dharura
4. Maendeleo Mkubwa
5. Maendeleo Mdogo
6. Business Loan
7. NBCSaccoss butua (advance)
8. Wastaafu loan
9. Wastaafu loan (dharura)
10. Sikuku"	Proper product and fees configuration.		
			LM-24	Loan officer	Loan Liquidation	The system should be able to liqudate liabilities		
			LM-25	loan officer	Credit bureau Integration	The system should be integrated with credit bureau 		
			LM-26	loan officer	Crucial attachments like application letter	The sytem should allow single and mutltiple attachment		
			LM-27	loan officer	Loans with exceptions or waivers	The system should provide queue for exception loans		
			LM-28	loan officer	Loan application form and BO	The system should accoLModate terms and conditions customers to accept		
			LM-29	loan officer	NBCSaccos butua 	Proper rules configuration. Should not go above member savings all time, Should not alllow if a member have any arreror, should not go above limit set a month 		
			LM-30	Manager	Creditbureau submission on line and Tempelate	Should be facilitated in the system		
			LM-31	Manager	Approval Procedures of loan	Loan status changes to "Approved", confirm loan processing workflows		
			LM-32	Accountant	Disburse approved loan	Loan balance created and disbursed amount deducted,accounting entry recorded,Fund transfers,income account created		
			LM-33	Accountant	Repay loan 	Loan balance reduced correctly,GL Effect,income effects		
			LM-34	Accountant	Loan penalty	Penalty automatically applied,on late repayments		
			LM-35	Accountant	overdue loans	aging analysis report		
								
			SV-01	Accountant	open savings account	saving account successfully created		
			SV-02	Accountant	Deposit savings	Balance updated, accounting entry,receipt generated Confirm maker checker control		
	3	Savings Management	SV-03	Accountant	Withdraw savings within balance	Balance updated correctly,accounting entry  confirm maker/checker controls		
			SV-04	Accountant	Withdraw savings exceeding balance	System prevents transaction with error message(no overdraft)		
			SV-05	Accountant	View savings statement	Accurate savings history displayed,export statements		
			SV-06	Accountant	Saving Transfers	Transfer from member one account to another-loans,shares,deposits,GL effects		
			SV-07	Accountant	Bulk Import	System must allow bulk saving imports		
			SV-08	Accountant	interest on savings calculation	interest on saving must be automatically calculated monthly,GL effects to payables		
			SM-01	Accountant	Share Purchase	Shares balance increases for member, GL updated and confirm share processing workflow		
	4	Share Management	SM-02	Accountant	Share Redemption	Member share balance decreases, payout recorded, GL reflects correctly		
			SM-03	Accountant	Share Statement	Report shows opening balance, share purchases, redemptions, and closing balance		
			SM-04	Accountant	Share Statement	Report shows opening balance, share purchases, redemptions, and closing balance		
			SM-05	Accountant	Check share certificate	Generate certificate		
								
			AC-01	Accountant	Double Entry Validation	Ensure each transaction follows double-entry principles		
	5	Accounting & Finance	AC-02	Accountant	Journal Entry Recording	manual journal entries ,balanced and posted correctly		
			AC-03	Accountant	General Ledger accounts	List of all individual ledger balances		
			AC-04	Accountant	Trial Balance Generation	All accounts balance (debits = credits).		
			AC-05	Accountant	Generate Financial Reports	Correct financial report displayed 		
			AC-06	Accountant	Chart of Accounts	Add,activate,reject dublicate		
			AC-07	Accountant	Reconciliations	Match records vs. bank statement(all reconciled correctly)		
			AC-08	Accountant	Audit Trail	Ensure all changes and transactions are logged		
			AC-09	Accountant	Generate member reports	Report shows all active members,inactive clients		
	6	Reports Management	RM-01	Accountant	Generate loan reports	Report on loan portifolio,aging analysis,disbursal,collection,overdue		
			RM-02	Accountant	Generate Saving Reports	Reports on deposits,withdraw,balances		
			RM-03	Accountant	Accounting Reports	Trial balance,note to accounts,income statement,balance sheet,changes in equity Cash flow,cash book report,		
			RM-04	Accountant	Share reports	list of shareholdings		
			RM-05	Accountant	Regulatory provisin based on BOT classification	Should be facilitated accordingly		
			RM-06	Accountant	Distribution based on BOT clasification	Should be facilitated accordingly		
			RM-07	Accountant	TCDC, BoT compliance reports 	Should be able to get all reports automatic via system		
			RM-08	Accountant	Backup system data	Backup completed successfully		
			RM-09	Accountant	Audit trial	Should be availabel		
	7	System 	SY-01	Accountant	Restore system from backup	System restored correctly		
			SY-02	Accountant	Loan repayments, Savings, shares via MNOs	Members should be able to make all crucial payment into control numbers via MNOs and Banks		
			SY-03	Accountant	Bank integration	Members should be able to Transfer and make  payments to control number.		
			SY-04	Accountant	Offline operations	The system should be flexible working offline once required		
			SY-05	Accountant	High availability deployment, patching, etc	The system should provide room for making changes and deployments.		
			SY-06	Accountant	Interfaces; web, App, USSd etc	The system should have interfaces such as  web, App, USSd		
			SY-07	Accountant	API Integrations: MNOs, banks etc	The system should have proper intergration between MNOs, control numbers and Bank accounts.		
			SY-08	Accountant	Support matrix	The system should have proper support matrix		
			SY-09	Accountant	System licenses	Licenses should be in place		
								
								
	8	Incomes	INC-01	Accountant	Record new income transaction	System records transaction and updates to respective income ledger		
			INC-02	Accountant	Verify automatic income recognition	Interest income posted automatically to income account (accruel concept)		
			INC-03	Accountant	Edit/Reverse income transaction	System allows correction with audit trail		
			INC-04	Accountant	Generate income report	Report shows correct income breakdown (interest, fees, other income)		
			INC-05	Accountant	Accrued incomes	all incomes must be recognized on accruel basis concept,effect to ledger accounts		
			INC-06	Accountant	Income Audit Trial	Each income shows date,user and references		
								
	9	Expenses	EXP-01	Accountant	Record expense payment	Expense recorded, cash/bank reduced,expense increased		
			EXP-02	Accountant	Approvals for expenses	Expense posts only after approval		
			EXP-03	Accountant	Expense categorization	System classifies into correct GL expense code		
			EXP-04	Accountant	Generate expense report	Report displays correct totals		
			EXP-05	Accountant	Journal expense posting	all accounts affected correctly		
			EXP-06	Accountant	Payroll management	system must be able to manage staff payroll		
			EXP-07	Accountant	Budgent control	Expenses within budget limit		
			EXP-08	Accountant	Accrued Expenses	Expense recognized, liability created under Accounts Payable		
								
	10	ASSETS	AST-01	Accountant	PPE management	acquisition,disposal,revaluation management of assets		
			AST-02	Accountant	Interest Receivable management	Proper management of accrued interest on loans		
			AST-03	Accountant	Debtors/Account Receivable	Proper reporting on account receivables over time		
								
								
	11	Equity and Liabilities	EL - 01	Accountant	Accounts payable	Proper listing of payables over time		
			EL - 02	Accountant	Settlement of accounts payable	Accounts Payable reduced, cash/bank reduced		
								
								
	12	SECURITY AND ACCESS CONTROL	SEC-01	manager	User login	Login with valid credentials-access granted		
			SEC-02	manager	Invalid login	Login with wrong Credentials-Access denied or error		
			SEC-03	manager	Role-based access	Attempt restricted action -system denies action		
			SEC-04	manager	Audit trail	All transactions/actions logged correctly		
								