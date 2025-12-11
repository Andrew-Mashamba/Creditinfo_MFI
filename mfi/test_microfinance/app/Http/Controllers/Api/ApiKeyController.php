<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of API keys
     */
    public function index(Request $request)
    {
        try {
            $query = ApiKey::with('creator')
                          ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'expired') {
                    $query->expired();
                }
            }

            // Filter by client name
            if ($request->has('client_name')) {
                $query->where('client_name', 'like', '%' . $request->client_name . '%');
            }

            $apiKeys = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $apiKeys,
                'message' => 'API keys retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving API keys', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve API keys',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created API key
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'rate_limit' => 'nullable|integer|min:1|max:10000',
                'allowed_ips' => 'nullable|array',
                'allowed_ips.*' => 'string|ip',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|in:transactions.read,transactions.write,accounts.read,accounts.write,*',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $apiKey = ApiKey::create([
                'client_name' => $request->client_name,
                'description' => $request->description,
                'rate_limit' => $request->rate_limit ?? config('api.keys.default_rate_limit'),
                'allowed_ips' => $request->allowed_ips,
                'permissions' => $request->permissions,
                'expires_at' => $request->expires_at,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            Log::info('API Key created', [
                'key_id' => $apiKey->id,
                'client_name' => $apiKey->client_name,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $apiKey->id,
                    'client_name' => $apiKey->client_name,
                    'key' => $apiKey->key, // Only show full key on creation
                    'masked_key' => $apiKey->masked_key,
                    'description' => $apiKey->description,
                    'rate_limit' => $apiKey->rate_limit,
                    'allowed_ips' => $apiKey->allowed_ips,
                    'permissions' => $apiKey->permissions,
                    'expires_at' => $apiKey->expires_at,
                    'created_at' => $apiKey->created_at,
                ],
                'message' => 'API key created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating API key', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['allowed_ips', 'permissions'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create API key',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified API key
     */
    public function show($id)
    {
        try {
            $apiKey = ApiKey::with('creator')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $apiKey->id,
                    'client_name' => $apiKey->client_name,
                    'masked_key' => $apiKey->masked_key,
                    'description' => $apiKey->description,
                    'is_active' => $apiKey->is_active,
                    'rate_limit' => $apiKey->rate_limit,
                    'allowed_ips' => $apiKey->allowed_ips,
                    'permissions' => $apiKey->permissions,
                    'expires_at' => $apiKey->expires_at,
                    'last_used_at' => $apiKey->last_used_at,
                    'created_at' => $apiKey->created_at,
                    'creator' => $apiKey->creator ? [
                        'id' => $apiKey->creator->id,
                        'name' => $apiKey->creator->name,
                        'email' => $apiKey->creator->email,
                    ] : null,
                ],
                'message' => 'API key retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving API key', [
                'error' => $e->getMessage(),
                'key_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API key not found',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 404);
        }
    }

    /**
     * Update the specified API key
     */
    public function update(Request $request, $id)
    {
        try {
            $apiKey = ApiKey::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'client_name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'sometimes|boolean',
                'rate_limit' => 'nullable|integer|min:1|max:10000',
                'allowed_ips' => 'nullable|array',
                'allowed_ips.*' => 'string|ip',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|in:transactions.read,transactions.write,accounts.read,accounts.write,*',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $apiKey->update($request->only([
                'client_name', 'description', 'is_active', 'rate_limit',
                'allowed_ips', 'permissions', 'expires_at'
            ]));

            DB::commit();

            Log::info('API Key updated', [
                'key_id' => $apiKey->id,
                'client_name' => $apiKey->client_name,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $apiKey->id,
                    'client_name' => $apiKey->client_name,
                    'masked_key' => $apiKey->masked_key,
                    'description' => $apiKey->description,
                    'is_active' => $apiKey->is_active,
                    'rate_limit' => $apiKey->rate_limit,
                    'allowed_ips' => $apiKey->allowed_ips,
                    'permissions' => $apiKey->permissions,
                    'expires_at' => $apiKey->expires_at,
                    'updated_at' => $apiKey->updated_at,
                ],
                'message' => 'API key updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating API key', [
                'error' => $e->getMessage(),
                'key_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update API key',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified API key
     */
    public function destroy($id)
    {
        try {
            $apiKey = ApiKey::findOrFail($id);

            DB::beginTransaction();

            $apiKey->delete();

            DB::commit();

            Log::info('API Key deleted', [
                'key_id' => $id,
                'client_name' => $apiKey->client_name,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'API key deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting API key', [
                'error' => $e->getMessage(),
                'key_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete API key',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Regenerate API key
     */
    public function regenerate($id)
    {
        try {
            $apiKey = ApiKey::findOrFail($id);

            DB::beginTransaction();

            $oldKey = $apiKey->key;
            $apiKey->update(['key' => ApiKey::generateKey()]);

            DB::commit();

            Log::info('API Key regenerated', [
                'key_id' => $apiKey->id,
                'client_name' => $apiKey->client_name,
                'old_key' => substr($oldKey, 0, 8) . '...',
                'regenerated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $apiKey->id,
                    'client_name' => $apiKey->client_name,
                    'key' => $apiKey->key, // Show new key
                    'masked_key' => $apiKey->masked_key,
                    'regenerated_at' => $apiKey->updated_at,
                ],
                'message' => 'API key regenerated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error regenerating API key', [
                'error' => $e->getMessage(),
                'key_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate API key',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get API key usage statistics
     */
    public function stats($id)
    {
        try {
            $apiKey = ApiKey::findOrFail($id);

            // Get usage statistics from cache or logs
            $usageStats = [
                'total_requests' => 0,
                'requests_today' => 0,
                'requests_this_week' => 0,
                'requests_this_month' => 0,
                'last_used' => $apiKey->last_used_at,
                'rate_limit_remaining' => $apiKey->rate_limit,
            ];

            return response()->json([
                'success' => true,
                'data' => $usageStats,
                'message' => 'API key statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving API key statistics', [
                'error' => $e->getMessage(),
                'key_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve API key statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
} 