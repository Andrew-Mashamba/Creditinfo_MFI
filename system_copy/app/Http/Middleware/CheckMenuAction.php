<?php

namespace App\Http\Middleware;

use App\Models\RoleMenuAction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MenuAction;

class CheckMenuAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $action
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $action)
    {
        Log::info('Checking menu action', [
            'user_id' => auth()->id(),
            'menu_action' => $action,
            'route' => $request->route()->getName()
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::warning('Unauthenticated user attempting to access protected action', [
                'action' => $action,
                'ip' => $request->ip()
            ]);
            return redirect()->route('login');
        }

        if (!$user->sub_role) {
            Log::error('User without sub_role attempting to access protected action', [
                'user_id' => $user->id,
                'action' => $action
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get the menu ID from the route
        $menuId = $request->route('menu_id') ?? $request->input('menu_id');

        if (!$menuId) {
            Log::error('Menu ID not provided for action check', [
                'user_id' => $user->id,
                'action' => $action
            ]);
            return response()->json(['error' => 'Menu ID required'], 400);
        }

        // Check if user has permission for this action
        $hasPermission = RoleMenuAction::where('sub_role', $user->sub_role)
            ->where('menu_id', $menuId)
            ->whereJsonContains('allowed_actions', $action)
            ->exists();

        if (!$hasPermission) {
            Log::warning('User attempted unauthorized action', [
                'user_id' => $user->id,
                'sub_role' => $user->sub_role,
                'menu_id' => $menuId,
                'action' => $action
            ]);
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        Log::info('Menu action permission granted', [
            'user_id' => auth()->id(),
            'menu_id' => $menuId,
            'action' => $action
        ]);

        return $next($request);
    }
}
