<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;

class CheckMenuPermission
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
        if (!PermissionHelper::userCan($action)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action'], 403);
            }
            
            session()->flash('error', 'You do not have permission to perform this action.');
            return redirect()->back();
        }

        return $next($request);
    }
} 