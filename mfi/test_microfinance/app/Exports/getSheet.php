<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;


use App\Models\cashbooknonmatchingstore;
use App\Models\nmbtransactionsnonmatchingstore;
use App\Models\uchumitransactionsnonmatchingstore;


use App\Models\crdbtransactionsnonmatchingstore;
use Maatwebsite\Excel\Concerns\WithHeadings;



class getSheet implements FromQuery, WithTitle, WithHeadings
{
private $page;
private $startDate;
private $endDatex;
private $thirdPart;

        public function __construct($page,$startDate,$endDate,$thirdPart)
        {
            $this->page = $page;
            $this->startDate  = $startDate;
            $this->endDatex = $endDate;
            $this->thirdPart  = $thirdPart;
        }

        /**
        * @return Builder
        */
        public function query()
        {

            if($this->page == 1){ //// Un presented Cheque
                if($this->thirdPart == 'CRDB'){
                    return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('transaction_amount', '>', 0)
                        ->where('institution', '=', 'CRDB');
                }
                if($this->thirdPart == 'NMB'){
                    return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('transaction_amount', '>', 0)
                        ->where('institution', '=', 'NMB');
                }
                if($this->thirdPart == 'UCHUMI'){
                    return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')

                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('transaction_amount', '>', 0)
                        ->where('institution', '=', 'UCHUMI');
                }
            }
            if($this->page == 2){ //// directDeposit
                if($this->thirdPart == 'CRDB'){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('credit', '>', 0)
                        ->where(function($q) {
                            $q->where('details', 'like', '%CASH DEPOSIT%')
                                ->orWhere('details', 'like', '%CASH DEPOSITS%')
                                ->orWhere('details', 'like', '%TRANSFER%')
                                ->orWhere('details', 'like', '%ELCT SAVINGS%')
                                ->orWhere('details', 'like', '%AIRTEL MONEY DEPOSIT%')
                                ->orWhere('details', 'like', '%OMNFT%')
                                ->orWhere('details', 'like', '%CHQ. NO.%')
                                ->orWhere('details', 'like', '%From 0%')
                                ->orWhere('details', 'like', '%FUND TRANS FROM%')
                                ->orWhere('details', 'like', '%TIGOPESA C2B%')
                                ->orWhere('details', 'like', '%M PESA DEPOSIT%')
                                ->orWhere('details', 'like', '%REVERSAL%');
                        });
                }
                if($this->thirdPart == 'NMB'){


                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('credit', '>', 0)
                        ->where(function($q) {
                            $q->where('details', 'like', '%Cash Deposit%')
                                ->orWhere('details', 'like', '%Standing Instruction Transfer%')
                                ->orWhere('details', 'like', '%Account to Account Transfer%')
                                ->orWhere('details', 'like', '%Funds Transfer%')
                                ->orWhere('details', 'like', '%TIPS Payments%');
                        });
                }
                if($this->thirdPart == 'UCHUMI'){

                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('credit', '>', 0)
                        ->where(function($q) {
                            $q->where('details', 'like', '%CASH DEPOSIT-BY%')
                                ->orWhere('details', 'like', '%Standing Instruction%')
                                ->orWhere('details', 'like', '%REVERSAL%');
                        });

                }

            }
            if($this->page == 3){ //// Standing order
                if($this->thirdPart == 'CRDB'){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('details',  'like', '%From 0%');
                }
                if($this->thirdPart == 'NMB'){
                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('details',  'like', '%Standing Instruction Transfer%');
                }
                if($this->thirdPart == 'UCHUMI'){
                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('details',  'like', '%Standing Instruction%');
                }

            }
            if($this->page == 4){ //// Suspense account
                if($this->thirdPart == 'CRDB'){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('credit', '>', 0)
                        ->where('details',  'not like', '%CASH DEPOSIT%')
                        ->where('details',  'not like', '%CASH DEPOSITS%')
                        ->where('details',  'not like', '%OMNFT%')
                        ->where('details',  'not like', '%CHQ. NO.%')
                        ->where('details',  'not like', '%From 0%')
                        ->where('details',  'not like', '%FUND TRANS FROM%')
                        ->where('details',  'not like', '%TIGOPESA C2B%')
                        ->where('details',  'not like', '%M PESA DEPOSIT%')
                        ->where('details',  'not like', '%REVERSAL%')
                        ->where('details', 'not like', '%TRANSFER%')
                        ->where('details', 'not like', '%ELCT SAVINGS%')
                        ->where('details', 'not like', '%AIRTEL MONEY DEPOSIT%');



                }
                if($this->thirdPart == 'NMB'){
                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('credit', '>', 0)
                        ->where('details',  'not like', '%Cash Deposit%')
                        ->where('details',  'not like', '%Standing Instruction Transfer%')
                        ->where('details',  'not like', '%Account to Account Transfer%')
                        ->where('details',  'not like', '%Funds Transfer%')
                        ->where('details',  'not like', '%TIPS Payments%');
                }
                if($this->thirdPart == 'UCHUMI'){
                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('credit', '>', 0)
                        ->where('details',  'not like', '%CASH DEPOSIT-BY%')
                        ->where('details',  'not like', '%Standing Instruction%')
                        ->where('details',  'not like', '%REVERSAL%');
                }

            }
            if($this->page == 5){ //// Uncredited cheque
                if($this->thirdPart == 'CRDB'){

                    return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('transaction_amount', '<', 0)
                        ->where('institution', '=', 'CRDB');

                }
                if($this->thirdPart == 'NMB'){

                    return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('transaction_amount', '<', 0)
                        ->where('institution', '=', 'NMB');

                }
                if($this->thirdPart == 'UCHUMI'){
                    return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('transaction_amount', '<', 0)
                        ->where('institution', '=', 'UCHUMI');
                }

            }
            if($this->page == 6){ //// Bank charges
                if($this->thirdPart == 'CRDB'){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where(function($q) {
                            $q->where('details', 'like', '%Cash withdrawal charges%')
                                ->orWhere('details', 'like', '%Monthly Maintenance Fee%');
                        });
                }
                if($this->thirdPart == 'NMB'){
                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('details', 'not like', '%VAT Payable on Comm and Fees%')
                        ->where(function($q) {
                            $q->where('details', 'like', '%fees%')
                                ->orWhere('details', 'like', '%Charge%')
                                ->orWhere('details', 'like', '%Commission%');
                        });
                }
                if($this->thirdPart == 'UCHUMI'){
                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where(function($q) {
                            $q->where('details', 'like', '%Monthly Service Charge%')
                                ->orWhere('details', 'like', '%charges%');
                        });
                }

            }
            if($this->page == 7){ //// Taxes

                if($this->thirdPart == 'CRDB'){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where(function($q) {
                            $q->where('details', 'like', '%VAT%')
                                ->orWhere('details', 'like', '%GOVERNMENT%');
                        });
                }
                if($this->thirdPart == 'NMB'){
                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('details', 'like', '%VAT Payable on Comm and Fees%');
                }
                if($this->thirdPart == 'UCHUMI'){
                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('details', 'like', '%Vat&Excise%');
                }


            }
            if($this->page == 8){ //// Direct payments


                if($this->thirdPart == 'CRDB'){

                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('debit', '>', 0)
                        ->where(function($q) {
                            $q->where('details', 'like', '%FUND TRANS TO%')
                                ->orWhere('details', 'like', '%GePG%');
                        });

                }
                if($this->thirdPart == 'NMB'){
                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('debit', '>', 0)
                        ->where(function($q) {
                            $q->where('details', 'like', '%Cash Cheque%')
                                ->orWhere('details', 'like', '%Cheque Deposit%')
                                ->orWhere('details', 'like', '%Account to Account Transfer%')
                                ->orWhere('details', 'like', '%Incoming EFT%');
                        });
                }
                if($this->thirdPart == 'UCHUMI'){
                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where(function($q) {
                            $q->where('details', 'like', '%CASH WITHDRAWAL-Cheque%')
                                ->orWhere('details', 'like', '%FUND TRANSFER%')
                                ->orWhere('details', 'like', '%EFT%')
                                ->orWhere('details', 'like', '%Current Accounts Periodic Interest%')
                                ->orWhere('details', 'like', '%TISS:%');
                        });
                }


            }
            if($this->page == 9){ //// Cash in transit

                if($this->thirdPart == 'CRDB'){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('debit', '>', 0)
                        ->where('details', 'not like', '%FUND TRANS TO%')
                        ->where('details', 'not like', '%GePG%')
                        ->where('details', 'not like', '%VAT%')
                        ->where('details', 'not like', '%Cash withdrawal charges%')
                        ->where('details', 'not like', '%Monthly Maintenance Fee%')
                        ->where('details', 'not like', '%GOVERNMENT%');
                }
                if($this->thirdPart == 'NMB'){
                    return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('debit', '>', 0)
                        ->where('details', 'not like', '%Cash Cheque%')
                        ->where('details', 'not like', '%Cheque Deposit%')
                        ->where('details', 'not like', '%fees%')
                        ->where('details', 'not like', '%Charge%')
                        ->where('details', 'not like', '%Commission%')
                        ->where('details', 'not like', '%VAT Payable on Comm and Fees%')
                        ->where('details', 'not like', '%Account to Account Transfer%')
                        ->where('details', 'not like', '%Incoming EFT%');
                }
                if($this->thirdPart == 'UCHUMI'){
                    return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')
                        ->where('value_date', '>=', $this->startDate)
                        ->where('value_date', '<=', $this->endDatex)
                        ->where('debit', '>', 0)
                        ->where('details', 'not like', '%CASH WITHDRAWAL-Cheque%')
                        ->where('details', 'not like', '%FUND TRANSFER%')
                        ->where('details', 'not like', '%EFT%')
                        ->where('details', 'not like', '%Current Accounts Periodic Interest%')
                        ->where('details', 'not like', '%charges%')
                        ->where('details', 'not like', '%Monthly Service Charge%')
                        ->where('details', 'not like', '%Vat&Excise%')
                        ->where('details', 'not like', '%TISS:%');
                }


            }


            return null;


        }

        /**
        * @return string
        */
        public function title(): string
        {


            if($this->page == 1){ //// Un presented Cheque
                return 'Un presented Cheque';
            }
            if($this->page == 2){ //// directDeposit
                return 'Direct Deposits';
            }
            if($this->page == 3){ //// Standing order
                return 'Standing order';
            }
            if($this->page == 4){ //// Suspense account
                return 'Suspense account';
            }
            if($this->page == 5){ //// Uncredited cheque
                return 'Uncredited cheque';
            }
            if($this->page == 6) { //// Bank charges
                return 'Bank charges';
            }
            if($this->page == 7){ //// Taxes
                return 'Taxes';
            }
            if($this->page == 8){ //// Direct payments
                return 'Direct payments';
            }
            if($this->page == 9){ //// Cash in transit
                return 'Cash in transit';
            }



            return 'Empty';
        }


    public function headings(): array
    {

        if($this->page == 1){ //// Un presented Cheque
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 2){ //// directDeposit
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","AMOUNT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 3){ //// Standing order
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 4){ //// Suspense account
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 5){ //// Uncredited cheque
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 6) { //// Bank charges
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 7){ //// Taxes
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 8){ //// Direct payments
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","AMOUNT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }
        if($this->page == 9){ //// Cash in transit
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }



        return null;

    }
}
