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

//class ExportTransactions implements FromCollection, FromQuery, WithHeadings
//{
//    /**
//    * @return \Illuminate\Support\Collection
//    */
//    public function collection(): \Illuminate\Support\Collection
//    {
//        if(Session::get('fileToDownload') =='NMB'){
//            //return Transactions::all();
//        }elseif (Session::get('fileToDownload') =='CRDB'){
//            return CrdbNonMatching::all();
//        }elseif (Session::get('fileToDownload') =='UCHUMI'){
//            //return Transactions::all();
//        }else{
//            return CashBookNonMatching::all();
//        }
//
//        //return User::select('name','email')->get();
//    }
//
//
//}

class ExportTransactionsAll implements FromQuery, WithHeadings
{
    //use Exportable;


    public function query()
    {
        //dd(Session::get('fileToDownload'));
                if(Session::get('fileToDownload') =='NMB'){
                    //return Transactions::all();
                }elseif (Session::get('fileToDownload') =='CRDB'){
                    if(Session::get('committed')){
                    return crdbtransactionsnonmatchingstore::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at');
                    }else{
                        return CrdbNonMatching::select('order_number','reference_number','value_date','credit','debit','details','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }
                }elseif (Session::get('fileToDownload') =='UCHUMI'){
                    //return Transactions::all();
                }else{
                    if(Session::get('committed')){
                        return cashbooknonmatchingstore::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at');
                    }else{
                        return CashBookNonMatching::select('order_number','reference_number','value_date','transaction_amount','description','institution','created_at')->where('order_number', Session::get('orderNumber'));
                    }

                }



    }

    public function headings(): array
    {
        if(Session::get('fileToDownload') =='NMB'){
            //return Transactions::all();
        }elseif (Session::get('fileToDownload') =='CRDB'){

            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","CREDIT","DEBIT","DESCRIPTION", "SOURCE", "SESSION DATE"];

        }elseif (Session::get('fileToDownload') =='UCHUMI'){
            //return Transactions::all();
        }else{
            return ["SESSION ID", "REFERENCE NUMBER", "VALUE DATE","AMOUNT","DESCRIPTION", "SOURCE", "SESSION DATE"];
        }

    return null;
    }
}
