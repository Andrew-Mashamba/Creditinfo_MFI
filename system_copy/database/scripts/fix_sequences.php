<?php

/**
 * Fix PostgreSQL Sequences Script
 * 
 * This script fixes auto-increment sequences that are out of sync in PostgreSQL.
 * Run this when you get "duplicate key value violates unique constraint" errors.
 * 
 * Usage:
 * php database/scripts/fix_sequences.php
 */

// Check if we're running in Tinker or standalone
if (!function_exists('app')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $app = require_once __DIR__ . '/../../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
}

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function fixAllSequences() {
    echo "\n====================================\n";
    echo "PostgreSQL Sequence Fixer\n";
    echo "====================================\n\n";
    
    try {
        // Get all tables with sequences
        $tables = DB::select("
            SELECT 
                table_name,
                column_name,
                column_default
            FROM information_schema.columns
            WHERE table_schema = 'public'
            AND column_default LIKE 'nextval%'
            ORDER BY table_name
        ");
        
        $fixedCount = 0;
        $checkedCount = 0;
        
        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $columnName = $table->column_name;
            
            // Skip if table doesn't exist
            if (!Schema::hasTable($tableName)) {
                continue;
            }
            
            $checkedCount++;
            
            // Get max ID from table
            $maxId = DB::table($tableName)->max($columnName) ?? 0;
            
            // Get sequence name from column default
            preg_match("/nextval\('([^']+)'/", $table->column_default, $matches);
            if (empty($matches[1])) {
                continue;
            }
            $sequenceName = $matches[1];
            
            // Get current sequence value
            try {
                $sequenceResult = DB::select("SELECT last_value FROM {$sequenceName}");
                $currentSequenceValue = $sequenceResult[0]->last_value ?? 0;
            } catch (\Exception $e) {
                echo "⚠️  Could not check sequence {$sequenceName} for table {$tableName}\n";
                continue;
            }
            
            // Check if sequence needs fixing
            if ($maxId >= $currentSequenceValue) {
                $newSequenceValue = $maxId + 1;
                
                // Fix the sequence
                DB::statement("SELECT setval('{$sequenceName}', {$newSequenceValue}, false)");
                
                echo "✅ Fixed: {$tableName}.{$columnName} - Sequence set to {$newSequenceValue} (was {$currentSequenceValue})\n";
                $fixedCount++;
            } else {
                echo "✓ OK: {$tableName}.{$columnName} - Sequence ({$currentSequenceValue}) > Max ID ({$maxId})\n";
            }
        }
        
        echo "\n====================================\n";
        echo "Summary:\n";
        echo "====================================\n";
        echo "Tables checked: {$checkedCount}\n";
        echo "Sequences fixed: {$fixedCount}\n";
        
        if ($fixedCount > 0) {
            echo "\n🎉 All sequences have been synchronized!\n";
        } else {
            echo "\n✅ All sequences were already in sync!\n";
        }
        
        // Special check for approvals table
        echo "\n🔍 Verifying approvals table specifically...\n";
        $approvalsMax = DB::table('approvals')->max('id') ?? 0;
        $approvalsSeq = DB::select("SELECT last_value FROM approvals_id_seq")[0]->last_value ?? 0;
        echo "Approvals: Max ID = {$approvalsMax}, Sequence = {$approvalsSeq}\n";
        
        if ($approvalsMax >= $approvalsSeq) {
            $newSeq = $approvalsMax + 1;
            DB::statement("SELECT setval('approvals_id_seq', {$newSeq}, false)");
            echo "✅ Approvals sequence fixed: set to {$newSeq}\n";
        } else {
            echo "✅ Approvals sequence is OK\n";
        }
        
        return true;
        
    } catch (\Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// Function to fix a specific table's sequence
function fixSequence($tableName, $columnName = 'id') {
    try {
        // Get max ID
        $maxId = DB::table($tableName)->max($columnName) ?? 0;
        
        // Set sequence to max ID + 1
        $sequenceName = $tableName . '_' . $columnName . '_seq';
        $newValue = $maxId + 1;
        
        DB::statement("SELECT setval('{$sequenceName}', {$newValue}, false)");
        
        echo "✅ Fixed {$tableName}: Sequence set to {$newValue}\n";
        return true;
        
    } catch (\Exception $e) {
        echo "❌ Error fixing {$tableName}: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run the fix
if (php_sapi_name() === 'cli') {
    // If specific table is provided as argument
    if (isset($argv[1])) {
        $tableName = $argv[1];
        echo "\nFixing sequence for table: {$tableName}\n";
        fixSequence($tableName);
    } else {
        // Fix all sequences
        fixAllSequences();
    }
}