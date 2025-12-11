<?php

require_once '../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Generating Table Index Structure ===\n\n";

try {
    // Get all tables from the database
    $tables = DB::select("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'BASE TABLE'
        ORDER BY table_name
    ");
    
    $output = "<?php\n\n";
    $output .= "// Auto-generated table index structure\n";
    $output .= "// Generated on: " . date('Y-m-d H:i:s') . "\n";
    $output .= "// Total tables: " . count($tables) . "\n\n";
    $output .= "return [\n";
    
    foreach ($tables as $table) {
        $tableName = $table->table_name;
        
        // Get actual fields for this table
        $fields = [];
        $columns = DB::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = ? 
            ORDER BY ordinal_position
        ", [$tableName]);
        
        foreach ($columns as $column) {
            $fields[] = "'" . $column->column_name . "'";
        }
        
        // Generate the table index structure
        $output .= "    '{$tableName}' => [\n";
        $output .= "        'description' => '', // TODO: Add description for {$tableName}\n";
        $output .= "        'keywords' => [], // TODO: Add keywords for {$tableName}\n";
        $output .= "        'fields' => [" . implode(', ', $fields) . "],\n";
        $output .= "        'sample_queries' => [], // TODO: Add sample queries for {$tableName}\n";
        $output .= "        'data_patterns' => '' // TODO: Add data patterns for {$tableName}\n";
        $output .= "    ],\n\n";
        
        echo "Generated structure for table: {$tableName} (" . count($fields) . " fields)\n";
    }
    
    $output .= "];\n";
    
    // Write to file
    $filename = 'generated_table_index.php';
    file_put_contents($filename, $output);
    
    echo "\n✅ Successfully generated table index structure!\n";
    echo "📁 File saved as: {$filename}\n";
    echo "📊 Total tables processed: " . count($tables) . "\n";
    echo "\n📝 Next steps:\n";
    echo "1. Review the generated file\n";
    echo "2. Fill in descriptions, keywords, sample queries, and data patterns\n";
    echo "3. Copy the relevant entries to your AiAgentService.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Generation Complete ===\n"; 