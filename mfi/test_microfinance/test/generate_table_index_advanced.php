<?php

require_once '../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Generating Advanced Table Index Structure ===\n\n";

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
    $output .= "// Auto-generated table index structure for AiAgentService.php\n";
    $output .= "// Generated on: " . date('Y-m-d H:i:s') . "\n";
    $output .= "// Total tables: " . count($tables) . "\n";
    $output .= "// Format: Ready to copy into getTableIndex() method\n\n";
    
    foreach ($tables as $table) {
        $tableName = $table->table_name;
        
        // Generate the table index structure in the exact format you want
        $output .= "            '{$tableName}' => [\n";
        $output .= "                'description' => '', // TODO: Add description for {$tableName}\n";
        $output .= "                'keywords' => [], // TODO: Add keywords for {$tableName}\n";
        $output .= "                'fields' => \$this->getTableFields('{$tableName}'),\n";
        $output .= "                'sample_queries' => [], // TODO: Add sample queries for {$tableName}\n";
        $output .= "                'data_patterns' => '' // TODO: Add data patterns for {$tableName}\n";
        $output .= "            ],\n\n";
        
        echo "Generated structure for table: {$tableName}\n";
    }
    
    // Write to file
    $filename = 'generated_table_index_advanced.txt';
    file_put_contents($filename, $output);
    
    echo "\n✅ Successfully generated advanced table index structure!\n";
    echo "📁 File saved as: {$filename}\n";
    echo "📊 Total tables processed: " . count($tables) . "\n";
    echo "\n📝 Usage instructions:\n";
    echo "1. Open the generated file\n";
    echo "2. Copy the entries you need\n";
    echo "3. Paste them into your getTableIndex() method in AiAgentService.php\n";
    echo "4. Fill in the descriptions, keywords, sample queries, and data patterns\n";
    echo "5. The 'fields' will automatically use getTableFields() for dynamic extraction\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Generation Complete ===\n"; 