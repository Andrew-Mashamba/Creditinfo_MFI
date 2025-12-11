<?php

namespace App\Exports;

use App\Models\crdbtransactionsnonmatchingstore;
use App\Models\cashbooknonmatchingstore;
use App\Models\CrdbNonMatching;
use App\Models\CashBookNonMatching;
use Maatwebsite\Excel\Concerns\FromCollection;
use Session;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\UchumiNonMatching;
use App\Models\uchumitransactionsnonmatchingstore;
use App\Models\nmbtransactionsnonmatchingstore;
use App\Models\NmbNonMatching;


class ExportTransactions implements FromQuery, WithHeadings
{
    //use Exportable;


    public function query()
    {
        //dd(Session::get('fileToDownload'));
                if(Session::get('fileToDownload') =='NMB'){
                    if(Session::get('committed')){
                        return nmbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }else{
                        return NmbNonMatching::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }
                }elseif (Session::get('fileToDownload') =='CRDB'){
                    if(Session::get('committed')){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }else{
                        return CrdbNonMatching::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }
                }elseif (Session::get('fileToDownload') =='UCHUMI'){
                    if(Session::get('committed')){
                        return uchumitransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }else{
                        return UchumiNonMatching::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }
                }else{
                    if(Session::get('committed')){

                        return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }else{
                        return CashBookNonMatching::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }

                }



    }

    public function headings(): array
    {
        if(Session::get('fileToDownload') =='NMB'){
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }elseif (Session::get('fileToDownload') =='CRDB'){

            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];

        }elseif (Session::get('fileToDownload') =='UCHUMI'){
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }else{
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","AMOUNT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }

    return null;
    }
}
