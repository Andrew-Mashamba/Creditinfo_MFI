<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;

class GenerateAccountNumber {


    function luhn_checksum($number) {
        $digits = str_split($number);
        $sum = 0;
        $alt = false;
        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $n = $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10;
    }

  public function generate_account_number($branch_code, $product_code) {
    do {
        // Generate a 5-digit random number for the unique account identifier
        $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // Concatenate branch code, unique identifier, and product code
        $partial_account_number = $branch_code . $unique_identifier . $product_code;

        // Calculate the checksum digit
        $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;

        // Form the final 12-digit account number
        $full_account_number = $partial_account_number . $checksum;

        // Check for uniqueness using Laravel's Eloquent model
        $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();

    } while (!$is_unique);

    return $full_account_number;
}


}
