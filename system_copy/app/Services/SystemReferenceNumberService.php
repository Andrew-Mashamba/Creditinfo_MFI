<?php

namespace App\Services;


use App\Models\Account as Accounts;
use App\Models\AccountsModel;
use App\Models\Activity;
use App\Models\general_ledger;
use App\Models\GeneralLedger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\ApprovalAction;
 use App\Enums\ServiceTypeEnum;
class   SystemReferenceNumberService
{

    public function generateReferenceNumber($organizationId, $memberNumber, $service, $loadId = null)
    {

        $serviceCode = ServiceTypeEnum::getCode($service);


        $serviceCode = $serviceCode?? 'UNK';
        $uniquePart = date('YmdHis');

        $loadPart = $loadId ? '-' . $loadId : '';

        $referenceNumber = "{$organizationId}-{$memberNumber}-{$serviceCode}-{$uniquePart}{$loadPart}";

        return  $referenceNumber;

    }

}
