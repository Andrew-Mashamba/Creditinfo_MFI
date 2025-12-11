<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix malformed JSON data in process_code_configs table
        $configs = DB::table('process_code_configs')->get();
        
        foreach ($configs as $config) {
            $updates = [];
            
            // Fix first_checker_roles
            if ($config->first_checker_roles && is_string($config->first_checker_roles)) {
                $decoded = json_decode($config->first_checker_roles, true);
                if (is_array($decoded) && isset($decoded['value'])) {
                    // Extract the role IDs from the nested structure
                    $roleIds = json_decode($decoded['value'], true);
                    $updates['first_checker_roles'] = json_encode($roleIds);
                }
            }
            
            // Fix second_checker_roles
            if ($config->second_checker_roles && is_string($config->second_checker_roles)) {
                $decoded = json_decode($config->second_checker_roles, true);
                if (is_array($decoded) && isset($decoded['value'])) {
                    // Extract the role IDs from the nested structure
                    $roleIds = json_decode($decoded['value'], true);
                    $updates['second_checker_roles'] = json_encode($roleIds);
                }
            }
            
            // Fix approver_roles
            if ($config->approver_roles && is_string($config->approver_roles)) {
                $decoded = json_decode($config->approver_roles, true);
                if (is_array($decoded) && isset($decoded['value'])) {
                    // Extract the role IDs from the nested structure
                    $roleIds = json_decode($decoded['value'], true);
                    $updates['approver_roles'] = json_encode($roleIds);
                }
            }
            
            // Update the record if there are changes
            if (!empty($updates)) {
                DB::table('process_code_configs')
                    ->where('id', $config->id)
                    ->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes data, so there's no rollback needed
        // The original malformed data would need to be restored manually if needed
    }
};
