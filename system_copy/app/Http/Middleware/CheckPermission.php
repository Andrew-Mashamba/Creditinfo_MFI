<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @param  string|null  $department
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $department = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $departmentModel = null;
        if ($department) {
            $departmentModel = Department::where('code', $department)->first();
        }

        // Get conditions from request
        $conditions = [];
        if ($request->has('max_amount')) {
            $conditions['max_amount'] = $request->max_amount;
        }
        if ($request->has('allowed_departments')) {
            $conditions['allowed_departments'] = $request->allowed_departments;
        }

        if (!$user->hasPermission($permission, $departmentModel, $conditions)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
} 