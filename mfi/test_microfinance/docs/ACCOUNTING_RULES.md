Accounting Systems
IAS 1 – Presentation of Financial Statements

Purpose: Defines structure, minimum content, and principles for financial statements (Balance Sheet, P&L, Cash Flow, Equity Statement, Notes).

Key Requirements:

Fair presentation, going concern, accrual basis.

Minimum line items, comparative information.

AI Opportunities:

Auto-generate complete financial statements.

Detect missing disclosures or misclassified line items.

Real-time compliance validation.

IAS 2 – Inventories

Purpose: Valuation of inventory (raw materials, WIP, finished goods).

Key Requirements:

Cost measured using FIFO or Weighted Average.

Write down to Net Realisable Value (NRV) if lower.

AI Opportunities:

Predict NRV using market data & sales trends.

Detect abnormal stock valuation.

Automate write-down triggers.

IAS 7 – Statement of Cash Flows

Purpose: Classification of cash flows (operating, investing, financing).

Key Requirements:

Direct or indirect method allowed.

Disclose changes in cash equivalents.

AI Opportunities:

Auto-classify cash transactions into correct categories.

Generate forecasts of future cash flows.

Spot liquidity risks.

IAS 8 – Accounting Policies, Estimates and Errors

Purpose: Rules for applying policies, changing estimates, and correcting errors.

Key Requirements:

Retrospective restatement for errors & policy changes.

Prospective application for estimates.

AI Opportunities:

Flag inconsistent policy applications.

Detect potential errors (e.g., anomalies in prior year restatements).

Automate impact analysis of policy changes.

IAS 10 – Events after the Reporting Period

Purpose: Treatment of post-reporting events.

Key Requirements:

Adjusting events (conditions before year-end) vs. Non-adjusting (after).

Require disclosure or adjustment accordingly.

AI Opportunities:

Scan news/transactions for adjusting events (e.g., litigation).

Auto-flag disclosures for non-adjusting events.

IAS 12 – Income Taxes

Purpose: Current & deferred tax accounting.

Key Requirements:

Temporary differences give rise to deferred tax assets/liabilities.

Recognition of tax loss carryforwards.

AI Opportunities:

Automate deferred tax calculations.

Predict future tax exposure.

Flag aggressive tax positions.

IAS 16 – Property, Plant and Equipment (PPE)

Purpose: Recognition, depreciation, impairment of tangible assets.

Key Requirements:

Cost model vs. Revaluation model.

Depreciation method consistent with asset use.

AI Opportunities:

Predict useful lives based on usage/IoT data.

Trigger revaluation reminders.

Flag impairment risks.

IAS 19 – Employee Benefits

Purpose: Recognition of pension, retirement, and employee obligations.

Key Requirements:

Defined contribution vs. defined benefit plans.

Actuarial assumptions disclosed.

AI Opportunities:

Automate actuarial projections.

Predict future benefit obligations.

Spot inconsistencies in benefit reporting.

IAS 20 – Government Grants

Purpose: Recognition of grants/subsidies.

Key Requirements:

Recognize when reasonable assurance exists.

Match with related costs.

AI Opportunities:

Detect eligibility for grants.

Automate deferred income recognition.

IAS 21 – Foreign Exchange

Purpose: Treatment of FX transactions & foreign operations.

Key Requirements:

Translate at spot rate (initial), closing rate (balances).

Exchange differences recognized in P&L or OCI.

AI Opportunities:

Real-time currency rate feeds.

Auto-translate subsidiaries’ FS.

Flag FX volatility risks.

IAS 23 – Borrowing Costs

Purpose: Capitalization of borrowing costs related to qualifying assets.

Key Requirements:

Capitalize interest if directly attributable.

AI Opportunities:

Detect qualifying assets automatically.

Allocate borrowing costs correctly.

IAS 24 – Related Party Disclosures

Purpose: Transparency of related-party transactions.

Key Requirements:

Disclose nature, amount, outstanding balances.

AI Opportunities:

Use NLP/graph models to detect hidden related parties.

Flag unusual terms in transactions.

IAS 26 – Retirement Benefit Plans

Purpose: Reporting by pension plans.

AI Opportunities:

Automate valuation reports.

Predict future payouts.

IAS 27 – Separate Financial Statements

Purpose: Accounting for investments in subsidiaries, JVs, associates in separate FS.

Key Requirements:

Cost or IFRS 9 method.

AI Opportunities:

Auto-link consolidation with separate FS.

IAS 28 – Investments in Associates & JVs

Purpose: Equity method for associates/JVs.

AI Opportunities:

Automate equity accounting.

Detect impairment triggers.

IAS 29 – Hyperinflationary Economies

Purpose: Restatement of FS for inflation.

AI Opportunities:

Detect inflationary conditions automatically.

Auto-adjust FS with price indices.

IAS 32 – Financial Instruments: Presentation

Purpose: Classification of debt vs equity.

AI Opportunities:

NLP models to classify hybrid instruments.

Detect misclassified liabilities.

IAS 33 – Earnings per Share

Purpose: Calculation of basic & diluted EPS.

AI Opportunities:

Auto-calculate EPS from share data.

Simulate dilution effects.

IAS 34 – Interim Financial Reporting

Purpose: Minimum content for interim (quarterly/half-year) reports.

AI Opportunities:

Automate interim statement generation.

Compare interim vs annual trends.

IAS 36 – Impairment of Assets

Purpose: Ensure assets are not overstated.

AI Opportunities:

AI models to predict recoverable amounts.

Auto-flag impairment triggers (declining sales, obsolescence).

IAS 37 – Provisions & Contingent Liabilities

Purpose: Recognition of uncertain obligations.

AI Opportunities:

NLP to scan contracts/legal docs for contingent liabilities.

Predict probability/impact of provisions.

IAS 38 – Intangible Assets

Purpose: Recognition & amortization of intangibles (patents, goodwill, software).

AI Opportunities:

Auto-value software licenses.

Predict useful life of intangibles.

IAS 40 – Investment Property

Purpose: Recognition & valuation of real estate held for investment.

AI Opportunities:

AI-driven fair value models.

Detect misclassified property.

IAS 41 – Agriculture

Purpose: Biological assets & agricultural produce.

AI Opportunities:

IoT/AI to fair-value livestock/crops.

Forecast yields and valuation.

✅ Takeaway:
For AI-driven accounting software, each IAS offers:

Rules → Constraints the system must enforce

Disclosures → Data AI can auto-fill or validate

Judgements → Areas where AI can assist with predictive models (e.g., impairment, fair value, provisions)

👉 Would you like me to design a system architecture (modules & AI features mapped to IAS standards) so you can see how these would sit inside your accounting software?

You said:
this were supposed to directions on how to build an accounting system..these directions will be used  by a coding AI to build the system
ChatGPT said:
Thought for 11s
Ias‑driven Accounting System Build Spec (for A Coding Ai)
IAS‑Driven Accounting System Build Spec (for a Coding AI)

Purpose: give a coding AI precise, buildable directions to implement an IFRS/IAS‑compliant general ledger and sub‑ledgers. Each IAS section includes Scope → Data Model → Rules/Algorithms → APIs → Validations → Tests.

Scope of standards covered: IAS 1, 2, 7, 8, 10, 12, 16, 19, 20, 21, 23, 24, 26, 27, 28, 29, 32, 33, 34, 36, 37, 38, 40, 41. (Use IFRS references where IAS cross‑links—e.g., IFRS 9 for instrument measurement—but keep this doc IAS‑anchored.)

0) Foundational Architecture (applies to all IAS)

Core Principles

Double‑entry, immutable ledger. No hard deletes; corrections via reversing entries and adjusting entries.

Event‑sourced postings; each transaction has origin_module, evidence_ref, prepared_by, approved_by, posted_at.

Periods: OPEN → SOFT_CLOSED (adjustments allowed) → HARD_CLOSED (locked). Close maintains an audit trail.

Multi‑entity, multi‑currency, multi‑GAAP via configuration.

Core Entities (minimum)

Account (id, code, name, type[Asset/Liab/Equity/Income/Expense], normal_balance, IFRS_map, parent_id, is_postable)

LedgerEntry (id, journal_id, account_id, debit, credit, currency, fx_rate, doc_date, post_date)

Journal (id, doc_type, doc_no, description, status, period_id, entity_id)

Document (id, type, metadata JSON, attachments[])

Period (id, start, end, status)

Dimension (cost_center, project, segment, customer_id, vendor_id, product_id, site_id)

DisclosureNote (id, standard_ref, period_id, entity_id, content JSON)

Shared Services

Policy Engine: JSON policies per IAS (e.g., inventory valuation method, depreciation method).

Measurement Engine: fair value, impairment, NRV, present value.

FX Engine: rates, translations, remeasurement.

Tax Engine: current/deferred calculations.

Consolidation Engine: eliminations, equity method.

Disclosure Composer: pulls required note data.

APIs (base)

POST /journals Create journal (draft)

POST /journals/{id}/post Validate + post entries

GET /trial-balance?period=...

GET /financials/{statement}?period=... (BS, P&L, CF, Equity)

POST /policies Set policy (standard_ref, key, value)

Validations (base)

Journal debits == credits; accounts exist & postable; period open; currency allowed; FX rate available when currency != functional.

Testing (base)

Unit: posting balance, locking periods, reversal mechanics. Integration: period close with adjusting entries.

IAS 1 – Presentation of Financial Statements

Scope: Structure/content for primary statements and notes.

Data Model

StatementLayout (id, type, rows[], mapping rules, subtotals, comparatives)

DisclosureNote (linked to layout rows for automatic note references)

Rules/Algorithms

Support classified vs liquidity‑based balance sheet.

Comparative column automatically pulls prior period; restatements flagged (link to IAS 8).

OCI segregation; reclassification adjustments tracked by reclass_tag on entries.

APIs

POST /layouts (define mapping from accounts/tags to lines)

GET /financials/balance-sheet?layout_id=...&period=...&comparative=true

Validations

Required line items present when balances are non‑zero; consistent subtotals; OCI lines separated.

Tests

Layout renders with and without comparatives; reclass adjustments hit OCI correctly.

IAS 2 – Inventories

Scope: Costing and NRV write‑downs for inventories.

Data Model

InventoryItem (sku, category, valuation_method[FIFO|WAC], cost_components[material,labor,oh], impairment_reserve)

InventoryLot (sku, qty, unit_cost, received_at)

Rules/Algorithms

Cost flow: FIFO queue or weighted‑average (per site).

NRV = max(selling_price − completion_cost − selling_costs, 0). Write down via expense Inventory write‑down with allowance.

Reversals when NRV increases (to the extent of previous write‑down).

APIs

POST /inventory/receipts (create lots)

POST /inventory/issues (consume per method)

POST /inventory/valuation/run?period=... (calculates COGS, NRV tests, postings)

Validations

Negative stock prevention (configurable); method consistent per SKU.

Tests

FIFO chain correctness; NRV write‑down and reversal scenarios.

IAS 7 – Statement of Cash Flows

Scope: Classify cash flows as Operating/Investing/Financing; direct/indirect.

Data Model

CashFlowRule (pattern: account range, document type, tags → category)

Rules/Algorithms

Indirect method: start with profit, adjust non‑cash (depr/amort, impairments), working capital deltas; investing/financing from posting tags.

Direct method optional: aggregate cash receipts/payments from cash/bank journals.

APIs

POST /cashflow/rules define classification rules

GET /financials/cash-flow?method=indirect&period=...

Validations

Reconcile change in cash to balance sheet; disclose non‑cash transactions.

Tests

Working capital bridge equals delta BS; classification coverage percentages.

IAS 8 – Policies, Estimates and Errors

Scope: Changes in policy/estimates; error restatements.

Data Model

Policy (standard_ref, key, value, effective_from)

Restatement (type[policy_change|error|estimate_change], affected_periods[], retrospective[bool], journals[])

Rules/Algorithms

Policy change/error → retrospective journals to earliest comparative; estimate change → prospective from effective_from.

APIs

POST /restatements create workflow (review/approve/post)

Validations

Lock prevents silent overwrite; disclosure note required for material items.

Tests

Retrospective restatement rebuilds prior periods and comparatives.

IAS 10 – Events after the Reporting Period

Scope: Adjusting vs non‑adjusting events.

Data Model

SubsequentEvent (event_date, description, adjusting[bool], impact JSON, disclosure_required)

Rules/Algorithms

Adjusting → post to prior period if before authorization date; Non‑adjusting → disclosure only unless going‑concern impacted.

APIs

POST /subsequent-events → optional auto‑journal

Tests

Authorization date gate; disclosure rendering.

IAS 12 – Income Taxes

Scope: Current and deferred tax.

Data Model

TaxRate (jurisdiction, rate, valid_from)

TemporaryDifference (asset_liab_ref, carrying_amount, tax_base, difference_type, expected_reversal_period)

Rules/Algorithms

Deferred tax = temp diff × rate; recognize DTA only if probable future taxable profits; track tax loss carryforwards with expiry.

APIs

POST /tax/provision/run?period=...

Validations

OCI vs P&L tracking for items recognized outside P&L.

Tests

Recognition threshold toggles; losses utilization schedule.

IAS 16 – Property, Plant and Equipment

Scope: Recognition, depreciation, revaluation.

Data Model

Asset (id, class, cost, componentization[], method[StraightLine|Units|Reducing], useful_life, residual, reval_model[bool], reval_reserve)

AssetEvent (acq, enhancement, impairment, revaluation, disposal)

Rules/Algorithms

Component depreciation; revaluation to fair value with OCI reserve; derecognition on disposal.

APIs

POST /assets

POST /assets/{id}/depreciation/run?period=...

Tests

Component roll‑forward; revaluation increments/decrements accounting.

IAS 19 – Employee Benefits

Scope: Short‑term, DC, DB plans.

Data Model

BenefitPlan (type[DC|DB], assumptions JSON)

ActuarialValuation (DBO, plan_assets, service_cost, interest_cost, actuarial_gains_losses)

Rules/Algorithms

DC: expense contributions. DB: compute DBO using projected unit credit; OCI for remeasurements.

APIs

POST /benefits/valuation/run?period=...

Tests

DC vs DB posting flows; OCI segregation.

IAS 20 – Government Grants

Scope: Recognition and presentation.

Data Model

Grant (type[income|asset], conditions[], recognition_schedule, deferred_income_balance)

Rules/Algorithms

Recognize when reasonable assurance; income over related costs or depreciable life for asset grants.

APIs

POST /grants

POST /grants/{id}/recognize?period=...

IAS 21 – FX Effects

Scope: Transactions & translation.

Data Model

FxRate (pair, date, spot, closing, average)

ForeignOperation (functional_currency)

Rules/Algorithms

Transaction remeasurement at spot; monetary items revalued at closing with P&L; foreign ops translated to presentation currency with CTA in equity.

APIs

POST /fx/rates

POST /fx/revalue?period=...

POST /fx/translate-consolidation?period=...

Tests

CTA accumulation; realized vs unrealized gains.

IAS 23 – Borrowing Costs

Scope: Capitalization for qualifying assets.

Data Model

Borrowing (rate, effective_rate, start, end, specific[bool])

QualifyingAsset (asset_ref, capitalization_window)

Rules/Algorithms

Capitalize specific borrowings first; otherwise use weighted average capitalization rate; suspend during extended idle periods.

APIs

POST /borrowings

POST /borrowings/capitalize?period=...

Tests

Mixed specific/general pools; suspension periods.

IAS 24 – Related Party Disclosures

Scope: Identify, collect, disclose related party transactions.

Data Model

RelatedParty (entity/person, relationship_type, control_significant_influence flags)

Link to customers, vendors, KMPs.

Rules/Algorithms

Graph search over ownership/officer data; flag transactions with non‑market terms.

APIs

POST /related-parties

GET /disclosures/ias24?period=...

Tests

Coverage of transactions; exception list for arm’s length evidence.

IAS 26 – Retirement Benefit Plans

Scope: FS for plans themselves.

Data Model

PlanFinancials (net_assets_available, actuarial_present_value_of_promised_benefits)

Rules/Algorithms

Statement formats per IAS 26; disclosures of funding policy.

APIs

GET /plans/{id}/financials?period=...

IAS 27 – Separate Financial Statements

Scope: Parent’s separate FS.

Data Model

Investment (subsidiary_id, measurement[Cost|IFRS9], dividends_received)

Rules/Algorithms

Do not consolidate; use chosen measurement consistently across similar investments.

APIs

GET /separate-financials/balance-sheet?period=...

IAS 28 – Associates & JVs (Equity Method)

Data Model

Associate (ownership_pct, significant_influence[bool])

`Equit