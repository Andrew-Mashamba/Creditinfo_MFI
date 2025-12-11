<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;

class RegisterShare {


  public   static function registerShare($member_number,$no_of_share,$product_id){
         DB::table('share_registers')->insert([
            'client_number'=>$member_number,
            'shares'=>$no_of_share,
            'share_product_id'=>$product_id,
            'created_at'=>now(),
            'update_at'=>now()
         ]);
    }


}
