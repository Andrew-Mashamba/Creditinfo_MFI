<?php

namespace App\Http\Livewire\Loans;

use DOMDocument;
use Livewire\Component;



use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\Clients;
use App\Models\AccountsModel;
use App\Models\Branches;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\loan_images;
use App\Models\LoansModel;
use App\Models\Loan_sub_products;

class Decision extends Component
{


    use WithFileUploads;

    public $photo;

    public $collateral_type;
    public $collateral_description;
    public $daily_sales;
    public $loan;
    public $collateral_value;
    public $loan_sub_product;
    public $tenure;
    public $principle_amount;
    public $member;


    public $guarantor;
    public $disbursement_account;
    public $collection_account_loan_interest;
    public $collection_account_loan_principle;
    public $collection_account_loan_charges;
    public $collection_account_loan_penalties;
    public $principle_min_value;
    public $principle_max_value;
    public $min_term;
    public $max_term;
    public $interest_value;
    public $principle_grace_period;
    public $interest_grace_period;
    public $amortization_method;
    public $days_in_a_month;
    public $loan_id;
    public $loan_account_number;

    public $member_number;


    public $interest;
    public $business_licence_number;
    public $business_tin_number;
    public $business_inventory;
    public $cash_at_hand;

    public $cost_of_goods_sold;
    public $operating_expenses;
    public $monthly_taxes;
    public $other_expenses;
    public $monthly_sales;
    public $gross_profit;
    public $table;
    public $tablefooter;
    public $recommended_tenure;
    public $recommended_installment;
    public $recommended = true;
    public $business_age;




    public function boot()
    {

        $this->loan = LoansModel::where('id', Session::get('currentloanID'))->get();

        foreach ($this->loan as $theloan) {
            $this->loan_id = $theloan->loan_id;
            $this->loan_account_number = $theloan->loan_account_number;
            $this->loan_sub_product = $theloan->loan_sub_product;
            $this->member_number = $theloan->member_number;

            $this->guarantor = $theloan->guarantor;
            $this->principle_amount  = $theloan->principle_amount;
            $this->interest  = $theloan->interest;

            $this->business_licence_number = $theloan->business_licence_number;
            $this->business_tin_number  = $theloan->business_tin_number;
            $this->business_inventory  = $theloan->business_inventory;

            $this->cash_at_hand = $theloan->cash_at_hand;
            $this->daily_sales  = $theloan->daily_sales;
            $this->cost_of_goods_sold  = $theloan->cost_of_goods_sold;

            $this->operating_expenses = $theloan->operating_expenses;
            $this->monthly_taxes  = $theloan->monthly_taxes;
            $this->other_expenses  = $theloan->other_expenses;

            $this->collateral_value  = $theloan->collateral_value;
             $this->collateral_type  = $theloan->collateral_type;
            $this->tenure  = $theloan->tenure;
            $this->business_age  = $theloan->business_age;

        }

        $this->products = Loan_sub_products::where('product_id', $this->loan_sub_product)->get();

        foreach ($this->products as $product) {
            $this->disbursement_account = $product->disbursement_account;
            $this->collection_account_loan_interest = $product->collection_account_loan_interest;
            $this->collection_account_loan_principle = $product->collection_account_loan_principle;
            $this->collection_account_loan_charges = $product->collection_account_loan_charges;

            $this->collection_account_loan_penalties = $product->collection_account_loan_penalties;
            $this->principle_min_value  = $product->principle_min_value;
            $this->principle_max_value  = $product->principle_max_value;

            $this->min_term = $product->min_term;
            $this->max_term  = $product->max_term;
            $this->interest_value  = $product->interest_value;

            $this->principle_grace_period = $product->principle_grace_period;
            $this->interest_grace_period  = $product->interest_grace_period;
            $this->amortization_method  = $product->amortization_method;

            $this->days_in_a_month = $product->days_in_a_month;


        }

        $this->guarantor = Clients::where('membership_number', $this->guarantor)->get();
        $this->member = Clients::where('membership_number', $this->member_number)->get();


    }

    public function render()
    {

        LoansModel::where('id', Session::get('currentloanID'))->update([
            'principle_amount' => $this->principle_amount,
            'tenure' => $this->tenure
        ]);

        $this->loan = LoansModel::where('id', Session::get('currentloanID'))->get();

        foreach ($this->loan as $theloan) {

            $this->principle_amount  = $theloan->principle_amount;

            $this->tenure  = $theloan->tenure;

        }


        $this->monthly_sales = (double)$this->daily_sales * (double)$this->days_in_a_month;
        $this->gross_profit = $this->monthly_sales - (double)$this->cost_of_goods_sold;
        $this->net_profit = $this->gross_profit - (double)$this->monthly_taxes;
        $this->available_funds = ($this->net_profit - (double)$this->other_expenses)/2;

        $interest = $this->interest_value/12;



        if($this->recommended) {

            $this->print_schedule($this->principle_amount, $interest, $this->available_funds);

        }else {

            $payment = $this->calc_payment($this->principle_amount, $this->tenure, $interest, 2);

            $this->print_schedule($this->principle_amount, $interest, $payment);

        }




        return view('livewire.loans.decision');
    }


    public function actionBtns($x){
        if($x == 1){
            $this->recommended = false;
        }
        if($x == 2){
            $this->recommended = true;
        }
        if($x == 3){
            $this->commit();
        }

    }

    public function commit(){
        $this->recommended = true;
    }






    function calc_principal($payno, $int, $pmt)
    {
// check that required values have been supplied
        if (empty($payno)) {
            echo "<p class='error'>a value for NUMBER of PAYMENTS is required</p>";
            exit;
        } // if
        if (empty($int)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($pmt)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

// now do the calculation using this formula:

//******************************************
//             ((1 + INT) ** PAYNO) - 1
// PV = PMT * --------------------------
//            INT * ((1 + INT) ** PAYNO)
//******************************************

        $int    = $int / 100;        //convert to percentage
        $value1 = (pow((1 + $int), $payno)) - 1;
        $value2 = $int * pow((1 + $int), $payno);
        $pv     = $pmt * ($value1 / $value2);
        $pv     = number_format($pv, 2, ".", "");

        return $pv;

    } // calc_principal ==================================================================






    function calc_number($pv, $int, $pmt)
    {
// check that required values have been supplied
        if (empty($pv)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($int)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($pmt)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

// now do the calculation using this formula:

//******************************************
//         log(1 - INT * PV/PMT)
// PAYNO = ---------------------
//             log(1 + INT)
//******************************************

        $int    = $int / 100;
        $value1 = log(1 - $int * ($pv / $pmt));
        $value2 = log(1 + $int);
        $payno  = $value1 / $value2;
        $payno  = abs($payno);
        $payno  = number_format($payno, 0, ".", "");

        return $payno;

    } // calc_number =====================================================================

    function calc_rate($pv, $payno, $pmt)
    {
// check that required values have been supplied
        if (empty($pv)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($payno)) {
            echo "<p class='error'>a value for NUMBER of PAYMENTS is required</p>";
            exit;
        } // if
        if (empty($pmt)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

// now try and guess the value using the binary chop technique
        $GuessHigh   = (float)100;    // maximum value
        $GuessMiddle = (float)2.5;    // first guess
        $GuessLow    = (float)0;      // minimum value
        $GuessPMT    = (float)0;      // result of test calculation

        do {
            // use current value for GuessMiddle as the interest rate,
            // and set level of accurracy to 6 decimal places
            $GuessPMT = (float)calc_payment($pv, $payno, $GuessMiddle, 6);

            if ($GuessPMT > $pmt) {    // guess is too high
                $GuessHigh   = $GuessMiddle;
                $GuessMiddle = $GuessMiddle + $GuessLow;
                $GuessMiddle = $GuessMiddle / 2;
            } // if

            if ($GuessPMT < $pmt) {    // guess is too low
                $GuessLow    = $GuessMiddle;
                $GuessMiddle = $GuessMiddle + $GuessHigh;
                $GuessMiddle = $GuessMiddle / 2;
            } // if

            if ($GuessMiddle == $GuessHigh) break;
            if ($GuessMiddle == $GuessLow) break;

            $int = number_format($GuessMiddle, 9, ".", "");    // round it to 9 decimal places
            if ($int == 0) {
                echo "<p class='error'>Interest rate has reached zero - calculation error</p>";
                exit;
            } // if

        } while ($GuessPMT !== $pmt);

        return $int;

    } // calc_rate =======================================================================

    function calc_payment($pv, $payno, $int, $accuracy)
    {
// check that required values have been supplied
        if (empty($pv)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($payno)) {
            echo "<p class='error'>a value for NUMBER of PAYMENTS is required</p>";
            exit;
        } // if
        if (empty($int)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if

// now do the calculation using this formula:

//******************************************
//            INT * ((1 + INT) ** PAYNO)
// PMT = PV * --------------------------
//             ((1 + INT) ** PAYNO) - 1
//******************************************

        $int    = $int / 100;    // convert to a percentage
        $value1 = $int * pow((1 + $int), $payno);
        $value2 = pow((1 + $int), $payno) - 1;
        $pmt    = $pv * ($value1 / $value2);
// $accuracy specifies the number of decimal places required in the result
        $pmt    = number_format($pmt, $accuracy, ".", "");

        return $pmt;

    } // calc_payment ====================================================================

    function print_schedulex($balance, $rate, $payment)
    {
    // check that required values have been supplied
        if (empty($balance)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($rate)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($payment)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

        echo '<table border="1">';
        echo '<colgroup align="right" width="20">';
        echo '<colgroup align="right" width="115">';
        echo '<colgroup align="right" width="115">';
        echo '<colgroup align="right" width="115">';
        echo '<colgroup align="right" width="115">';
        echo '<tr><th>#</th><th>PAYMENT</th><th>INTEREST</th><th>PRINCIPAL</th><th>BALANCE</th></tr>';

        $count = 0;
        do {
            $count++;

            // calculate interest on outstanding balance
            $interest = $balance * $rate/100;

            // what portion of payment applies to principal?
            $principal = $payment - $interest;

            // watch out for balance < payment
            if ($balance < $payment) {
                $principal = $balance;
                $payment   = $interest + $principal;
            } // if

            // reduce balance by principal paid
            $balance = $balance - $principal;

            // watch for rounding error that leaves a tiny balance
            if ($balance < 0) {
                $principal = $principal + $balance;
                $interest  = $interest - $balance;
                $balance   = 0;
            } // if

            echo "<tr>";
            echo "<td>$count</td>";
            echo "<td>" .number_format($payment,   2, ".", ",") ."</td>";
            echo "<td>" .number_format($interest,  2, ".", ",") ."</td>";
            echo "<td>" .number_format($principal, 2, ".", ",") ."</td>";
            echo "<td>" .number_format($balance,   2, ".", ",") ."</td>";
            echo "</tr>";

            @$totPayment   = $totPayment + $payment;
            @$totInterest  = $totInterest + $interest;
            @$totPrincipal = $totPrincipal + $principal;

            if ($payment < $interest) {
                echo "</table>";
                echo "<p>Payment < Interest amount - rate is too high, or payment is too low</p>";
                exit;
            } // if

        } while ($balance > 0);

        echo "<tr>";
        echo "<td>&nbsp;</td>";
        echo "<td><b>" .number_format($totPayment,   2, ".", ",") ."</b></td>";
        echo "<td><b>" .number_format($totInterest,  2, ".", ",") ."</b></td>";
        echo "<td><b>" .number_format($totPrincipal, 2, ".", ",") ."</b></td>";
        echo "<td>&nbsp;</td>";
        echo "</tr>";
        echo "</table>";

    } // print_schedule ==================================================================


    /**
     * @throws \DOMException
     */
    function print_schedule($balance, $rate, $payment)
    {
        // check that required values have been supplied
        if (empty($balance)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($rate)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($payment)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if


        $datalist = array();
        $count = 0;
        do {
            $count++;

            // calculate interest on outstanding balance
            $interest = $balance * $rate/100;

            // what portion of payment applies to principal?
            $principal = $payment - $interest;

            // watch out for balance < payment
            if ($balance < $payment) {
                $principal = $balance;
                $payment   = $interest + $principal;
            } // if

            // reduce balance by principal paid
            $balance = $balance - $principal;

            // watch for rounding error that leaves a tiny balance
            if ($balance < 0) {
                $principal = $principal + $balance;
                $interest  = $interest - $balance;
                $balance   = 0;
            } // if



            $datalist[] = array(
                "Payment" => $payment,
                "Interest" => $interest,
                "Principle" => $principal,
                "balance" => $balance
            );

            @$totPayment   = $totPayment + $payment;
            @$totInterest  = $totInterest + $interest;
            @$totPrincipal = $totPrincipal + $principal;

            if ($payment < $interest) {
                echo "</table>";
                echo "<p>Payment < Interest amount - rate is too high, or payment is too low</p>";
                exit;
            } // if

        } while ($balance > 0);


        $datalistfooter[] = array(
            "Payment" => $totPayment,
            "Interest" => $totInterest,
            "Principle" => $totPrincipal,
            "balance" => $balance
        );



        $this->table = $datalist;
        $this->tablefooter = $datalistfooter;
        $this->recommended_tenure = $count;
        $this->recommended_installment = $payment;

    } // print_schedule ==================================================================















}

