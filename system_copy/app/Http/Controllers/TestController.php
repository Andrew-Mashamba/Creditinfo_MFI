<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleMenuAction;

class TestController extends Controller
{
    public function test()
    {
        return " test passed";
    }

    public function getUser(Request $request)
    {
        return $request->user();
    }

    public function checkMenuAction(Request $request)
    {
        $user = $request->user();

        if (!$user->sub_role) {
            return response()->json(['error' => 'User has no sub_role'], 403);
        }

        $action = $request->input('action');
        $menuId = $request->input('menu_id');

        if (!$action || !$menuId) {
            return response()->json(['error' => 'Action and menu_id are required'], 400);
        }

        $hasPermission = RoleMenuAction::where('sub_role', $user->sub_role)
            ->where('menu_id', $menuId)
            ->whereJsonContains('allowed_actions', $action)
            ->exists();

        if (!$hasPermission) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        return response()->json(['message' => 'Action authorized']);
    }
}