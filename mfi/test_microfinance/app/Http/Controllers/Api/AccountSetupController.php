<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountSetupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class AccountSetupController extends Controller
{
    protected $accountSetupService;

    public function __construct(AccountSetupService $accountSetupService)
    {
        $this->accountSetupService = $accountSetupService;
    }

    /**
     * Set up accounts for an institution
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setupAccounts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'institution_id' => 'required|integer|min:1'
            ]);

            $result = $this->accountSetupService->setAccount($request->institution_id);

            return response()->json([
                'success' => true,
                'message' => 'Account setup completed successfully',
                'data' => $result
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Account setup failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 