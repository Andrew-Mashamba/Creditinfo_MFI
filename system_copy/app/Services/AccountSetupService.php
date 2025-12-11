<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AccountsModel;
use Exception;

class AccountSetupService
{
    /**
     * Map of major category codes to account types
     */
    private $accountTypeMap = [
        '1000' => 'asset_accounts',
        '2000' => 'liability_accounts',
        '3000' => 'capital_accounts',
        '4000' => 'income_accounts',
        '5000' => 'expense_accounts'
    ];

    /**
     * Set up accounts for a given institution ID
     *
     * @param int $id The institution ID
     * @return array
     * @throws Exception
     */
    public function setAccount($id)
    {
        try {
            Log::info("Starting account setup process for institution ID: {$id}");
            
            // Truncate the accounts table
            DB::table('accounts')->truncate();
            Log::info("Truncated accounts table");
            
            // Drop and recreate GL_accounts table
            $this->recreateGLAccountsTable();
            
            // Create general accounts
            $this->createGeneralAccounts();
            Log::info("General accounts created successfully");

            // Get the file path
            $filePath = storage_path('txt/go.txt');
            Log::info("Reading account structure from file: {$filePath}");

            if (!File::exists($filePath)) {
                throw new Exception("Account structure file not found at: {$filePath}");
            }

            // Read the contents of the file
            $fileContents = File::get($filePath);
            $lines = explode("\n", $fileContents);
            Log::info("Successfully read " . count($lines) . " lines from account structure file");

            $stats = [
                'major_categories' => 0,
                'categories' => 0,
                'sub_categories' => 0,
                'accounts_created' => 0
            ];

            // Process each line
            foreach ($lines as $lineNumber => $line) {
                try {
                    $line = trim($line);
                    if (empty($line) || strpos($line, '#') === 0) continue;

                    $parts = explode('|', $line);
                    
                    // Skip if not enough parts
                    if (count($parts) < 3) {
                        Log::warning("Skipping line {$lineNumber}: Insufficient parts");
                        continue;
                    }

                    $type = trim($parts[0]);
                    
                    // Validate number of parts based on type
                    switch ($type) {
                        case 'MAJOR':
                            if (count($parts) < 3) {
                                Log::warning("Skipping MAJOR line {$lineNumber}: Expected 3 parts");
                                continue 2;
                            }
                            $code = trim($parts[1]);
                            $name = trim($parts[2]);
                            
                            // Create major account (level 1)
                            $majorAccountNumber = str_pad($id, 2, 0, STR_PAD_LEFT) . '' . str_pad(1, 2, 0, STR_PAD_LEFT) . $code . '0000';
                            $accountType = $this->accountTypeMap[$code] ?? 'asset_accounts';
                            
                            // Check if major account already exists
                            $existingAccount = AccountsModel::where('account_number', $majorAccountNumber)->first();
                            if (!$existingAccount) {
                                AccountsModel::create([
                                    'account_use' => 'internal',
                                    'institution_number' => $id,
                                    'branch_number' => 01,
                                    'client_number' => '0000',
                                    'product_number' => '',
                                    'sub_product_number' => '',
                                    'major_category_code' => $code,
                                    'category_code' => '0000',
                                    'sub_category_code' => '0000',
                                    'account_name' => $name,
                                    'account_number' => $majorAccountNumber,
                                    'type' => $accountType,
                                    'account_level' => 1,
                                    'status' => 'ACTIVE',
                                    'status' => 'ACTIVE',
                                    'parent_account_number' => null,
                                    'notes' => "Major Account",
                                ]);
                                
                                $stats['major_categories']++;
                                Log::info("Created major account: {$majorAccountNumber} - {$name}");
                            }
                            break;

                        case 'CATEGORY':
                            if (count($parts) < 4) {
                                Log::warning("Skipping CATEGORY line {$lineNumber}: Expected 4 parts");
                                continue 2;
                            }
                            $majorCode = trim($parts[1]);
                            $categoryCode = trim($parts[2]);
                            $name = trim($parts[3]);
                            
                            // Get parent major account
                            $parentMajorAccount = AccountsModel::where('major_category_code', $majorCode)
                                ->where('account_level', 1)
                                ->first();
                                
                            if ($parentMajorAccount) {
                                // Create category account (level 2)
                                $categoryAccountNumber = str_pad($id, 2, 0, STR_PAD_LEFT) . '' . str_pad(1, 2, 0, STR_PAD_LEFT) . $majorCode . $categoryCode;
                                
                                // Check if category account already exists
                                $existingAccount = AccountsModel::where('account_number', $categoryAccountNumber)->first();
                                if (!$existingAccount) {
                                    AccountsModel::create([
                                        'account_use' => 'internal',
                                        'institution_number' => $id,
                                        'branch_number' => 01,
                                        'client_number' => '0000',
                                        'product_number' => '',
                                        'sub_product_number' => '',
                                        'major_category_code' => $majorCode,
                                        'category_code' => $categoryCode,
                                        'sub_category_code' => '0000',
                                        'account_name' => $name,
                                        'account_number' => $categoryAccountNumber,
                                        'type' => $parentMajorAccount->type,
                                        'account_level' => 2,
                                        'status' => 'ACTIVE',
                                        'status' => 'ACTIVE',
                                        'parent_account_number' => $parentMajorAccount->account_number,
                                        'notes' => "Category Account",
                                    ]);
                                    
                                    $stats['categories']++;
                                    Log::info("Created category account: {$categoryAccountNumber} - {$name}");
                                }
                            }
                            break;

                        case 'SUBCAT':
                            if (count($parts) < 5) {
                                Log::warning("Skipping SUBCAT line {$lineNumber}: Expected 5 parts");
                                continue 2;
                            }
                            $majorCode = trim($parts[1]);
                            $categoryCode = trim($parts[2]);
                            $subCategoryCode = trim($parts[3]);
                            $name = trim($parts[4]);
                            
                            // Get parent category account
                            $parentCategoryAccount = AccountsModel::where('major_category_code', $majorCode)
                                ->where('category_code', $categoryCode)
                                ->where('account_level', 2)
                                ->first();
                                
                            if ($parentCategoryAccount) {
                                // Create sub-category account (level 3)
                                $subCategoryAccountNumber = str_pad($id, 2, 0, STR_PAD_LEFT) . '' . str_pad(1, 2, 0, STR_PAD_LEFT) . $majorCode . $categoryCode . $subCategoryCode;
                                
                                // Check if sub-category account already exists
                                $existingAccount = AccountsModel::where('account_number', $subCategoryAccountNumber)->first();
                                if (!$existingAccount) {
                                    AccountsModel::create([
                                        'account_use' => 'internal',
                                        'institution_number' => $id,
                                        'branch_number' => 01,
                                        'client_number' => '0000',
                                        'product_number' => '',
                                        'sub_product_number' => '',
                                        'major_category_code' => $majorCode,
                                        'category_code' => $categoryCode,
                                        'sub_category_code' => $subCategoryCode,
                                        'account_name' => $name,
                                        'account_number' => $subCategoryAccountNumber,
                                        'type' => $parentCategoryAccount->type,
                                        'account_level' => 3,
                                        'status' => 'ACTIVE',
                                        'status' => 'ACTIVE',
                                        'parent_account_number' => $parentCategoryAccount->account_number,
                                        'notes' => "Sub-Category Account",
                                    ]);
                                    
                                    $stats['sub_categories']++;
                                    $stats['accounts_created']++;
                                    Log::info("Created sub-category account: {$subCategoryAccountNumber} - {$name}");
                                }
                            }
                            break;
                            
                        default:
                            Log::warning("Skipping line {$lineNumber}: Unknown type '{$type}'");
                            continue 2;
                    }
                } catch (Exception $e) {
                    Log::error("Error processing line {$lineNumber}: " . $e->getMessage());
                    continue; // Skip this line and continue with the next one
                }
            }

            // Generate seeder file after successful account creation
            $seederResult = $this->generateAccountSeeder($id, $stats);
            
            Log::info("Account setup completed successfully", $stats);
            return [
                'success' => true,
                'message' => 'Account setup completed successfully',
                'stats' => $stats,
                'seeder_info' => $seederResult
            ];

        } catch (Exception $e) {
            Log::error("Account setup failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a seeder file containing all created accounts
     *
     * @param int $institutionId The institution ID
     * @param array $stats Setup statistics
     * @return array
     */
    private function generateAccountSeeder($institutionId, $stats)
    {
        try {
            Log::info("Starting seeder generation for institution ID: {$institutionId}");
            
            // Get all accounts for this institution
            $accounts = AccountsModel::where('institution_number', $institutionId)
                ->orderBy('account_level')
                ->orderBy('major_category_code')
                ->orderBy('category_code')
                ->orderBy('sub_category_code')
                ->get();
                
            // Get GL accounts
            $glAccounts = DB::table('GL_accounts')->get();
            
            $timestamp = now()->format('Y_m_d_His');
            $className = "Institution{$institutionId}AccountsSeeder_{$timestamp}";
            $fileName = "{$className}.php";
            $filePath = database_path("seeders/{$fileName}");
            
            // Generate seeder content
            $seederContent = $this->buildSeederContent($className, $institutionId, $accounts, $glAccounts, $stats);
            
            // Write the seeder file
            File::put($filePath, $seederContent);
            
            Log::info("Seeder file generated successfully: {$fileName}");
            
            return [
                'success' => true,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'class_name' => $className,
                'accounts_exported' => $accounts->count(),
                'gl_accounts_exported' => $glAccounts->count()
            ];
            
        } catch (Exception $e) {
            Log::error("Failed to generate seeder: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the seeder file content
     *
     * @param string $className
     * @param int $institutionId
     * @param \Illuminate\Database\Eloquent\Collection $accounts
     * @param \Illuminate\Support\Collection $glAccounts
     * @param array $stats
     * @return string
     */
    private function buildSeederContent($className, $institutionId, $accounts, $glAccounts, $stats)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\\Seeders;\n\n";
        $content .= "use Illuminate\\Database\\Console\\Seeds\\WithoutModelEvents;\n";
        $content .= "use Illuminate\\Database\\Seeder;\n";
        $content .= "use Illuminate\\Support\\Facades\\DB;\n";
        $content .= "use App\\Models\\AccountsModel;\n\n";
        $content .= "/**\n";
        $content .= " * Auto-generated seeder for Institution {$institutionId} accounts\n";
        $content .= " * Generated on: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= " * Total accounts: " . $accounts->count() . "\n";
        $content .= " * Major categories: " . $stats['major_categories'] . "\n";
        $content .= " * Categories: " . $stats['categories'] . "\n";
        $content .= " * Sub-categories: " . $stats['sub_categories'] . "\n";
        $content .= " */\n";
        $content .= "class {$className} extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     */\n";
        $content .= "    public function run(): void\n";
        $content .= "    {\n";
        $content .= "        // Clear existing accounts for this institution\n";
        $content .= "        AccountsModel::where('institution_number', {$institutionId})->delete();\n";
        $content .= "        DB::table('GL_accounts')->truncate();\n\n";
        
        // Add GL accounts
        $content .= "        // Create GL accounts\n";
        $content .= "        \$glAccounts = [\n";
        foreach ($glAccounts as $glAccount) {
            $content .= "            [\n";
            $content .= "                'account_code' => {$glAccount->account_code},\n";
            $content .= "                'account_name' => '" . addslashes($glAccount->account_name) . "',\n";
            $content .= "                'created_at' => now(),\n";
            $content .= "                'updated_at' => now()\n";
            $content .= "            ],\n";
        }
        $content .= "        ];\n";
        $content .= "        DB::table('GL_accounts')->insert(\$glAccounts);\n\n";
        
        // Add accounts
        $content .= "        // Create accounts\n";
        $content .= "        \$accounts = [\n";
        
        foreach ($accounts as $account) {
            $content .= "            [\n";
            $content .= "                'account_use' => '" . addslashes($account->account_use ?? '') . "',\n";
            $content .= "                'institution_number' => {$account->institution_number},\n";
            $content .= "                'branch_number' => {$account->branch_number},\n";
            $content .= "                'client_number' => '" . addslashes($account->client_number ?? '') . "',\n";
            $content .= "                'product_number' => '" . addslashes($account->product_number ?? '') . "',\n";
            $content .= "                'sub_product_number' => '" . addslashes($account->sub_product_number ?? '') . "',\n";
            $content .= "                'major_category_code' => '" . addslashes($account->major_category_code ?? '') . "',\n";
            $content .= "                'category_code' => '" . addslashes($account->category_code ?? '') . "',\n";
            $content .= "                'sub_category_code' => '" . addslashes($account->sub_category_code ?? '') . "',\n";
            $content .= "                'account_name' => '" . addslashes($account->account_name ?? '') . "',\n";
            $content .= "                'account_number' => '" . addslashes($account->account_number ?? '') . "',\n";
            $content .= "                'type' => '" . addslashes($account->type ?? '') . "',\n";
            $content .= "                'account_level' => {$account->account_level},\n";
            $content .= "                'status' => '" . addslashes($account->status ?? '') . "',\n";
            $content .= "                'status' => '" . addslashes($account->status ?? '') . "',\n";
            $content .= "                'parent_account_number' => " . ($account->parent_account_number ? "'" . addslashes($account->parent_account_number) . "'" : 'null') . ",\n";
            $content .= "                'notes' => '" . addslashes($account->notes ?? '') . "',\n";
            $content .= "                'created_at' => now(),\n";
            $content .= "                'updated_at' => now()\n";
            $content .= "            ],\n";
        }
        
        $content .= "        ];\n\n";
        $content .= "        // Insert accounts in batches to avoid memory issues\n";
        $content .= "        \$chunks = array_chunk(\$accounts, 100);\n";
        $content .= "        foreach (\$chunks as \$chunk) {\n";
        $content .= "            AccountsModel::insert(\$chunk);\n";
        $content .= "        }\n\n";
        $content .= "        \$this->command->info('Successfully seeded ' . count(\$accounts) . ' accounts for Institution {$institutionId}');\n";
        $content .= "    }\n";
        $content .= "}\n";
        
        return $content;
    }

    /**
     * Create general accounts in the GL_accounts table
     *
     * @return void
     * @throws Exception
     */
    private function createGeneralAccounts()
    {
        try {
            // Truncate the GL_accounts table before inserting
            DB::table('GL_accounts')->truncate();
            
            $accounts = [
                ['account_code' => 4000, 'account_name' => 'Revenue Account', 'created_at' => now(), 'updated_at' => now()],
                ['account_code' => 5000, 'account_name' => 'Expense Accounts', 'created_at' => now(), 'updated_at' => now()],
                ['account_code' => 1000, 'account_name' => 'Asset Account', 'created_at' => now(), 'updated_at' => now()],
                ['account_code' => 2000, 'account_name' => 'Liability Accounts', 'created_at' => now(), 'updated_at' => now()],
                ['account_code' => 3000, 'account_name' => 'Equity Accounts', 'created_at' => now(), 'updated_at' => now()],
            ];

            DB::table('GL_accounts')->insert($accounts);
            Log::info("Created " . count($accounts) . " general accounts");
        } catch (Exception $e) {
            Log::error("Failed to create general accounts: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Recreate the GL_accounts table
     *
     * @return void
     */
    private function recreateGLAccountsTable()
    {
        Schema::dropIfExists('GL_accounts');
        Schema::create('GL_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('account_code');
            $table->string('account_name');
            $table->timestamps();
        });
        Log::info("Recreated GL_accounts table");
    }

    /**
     * Recreate a table with the given name and schema
     *
     * @param string $tableName
     * @param callable $callback
     * @return void
     */
    private function recreateTable(string $tableName, callable $callback)
    {
        try {
            Schema::dropIfExists($tableName);
            Schema::create($tableName, $callback);
            Log::info("Recreated table: {$tableName}");
        } catch (Exception $e) {
            Log::error("Failed to recreate table {$tableName}: " . $e->getMessage());
            throw $e;
        }
    }
} 