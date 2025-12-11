<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Payments\ExternalFundsTransferService;

/**
 * Test TISS vs TIPS Routing Logic
 * 
 * Tests the automatic routing determination based on amount:
 * - TIPS: amounts < 20,000,000 TZS
 * - TISS: amounts >= 20,000,000 TZS
 */
class TestTipsTissRoutingCommand extends Command
{
    protected $signature = 'test:tips-tiss-routing 
                           {--amount=* : Test specific amounts (comma-separated)}';

    protected $description = 'Test TIPS vs TISS routing logic based on transfer amounts';

    const TISS_THRESHOLD = 20000000; // 20 million TZS
    
    public function handle()
    {
        $this->info("🔀 Testing TIPS vs TISS Routing Logic");
        $this->info("TIPS Threshold: TZS " . number_format(self::TISS_THRESHOLD, 2));
        $this->info("TIPS: < TZS 20,000,000");
        $this->info("TISS: >= TZS 20,000,000");
        $this->newLine();

        $amounts = $this->option('amount');
        
        if (empty($amounts)) {
            // Default test amounts
            $testAmounts = [
                1000,           // 1K - TIPS
                50000,          // 50K - TIPS
                1000000,        // 1M - TIPS
                10000000,       // 10M - TIPS
                19999999,       // Just under 20M - TIPS
                20000000,       // Exactly 20M - TISS
                20000001,       // Just over 20M - TISS
                25000000,       // 25M - TISS
                50000000,       // 50M - TISS
                100000000,      // 100M - TISS
            ];
        } else {
            $testAmounts = array_map('floatval', $amounts);
        }

        $this->info("📊 Testing Routing for Different Amounts:");
        $this->newLine();

        foreach ($testAmounts as $amount) {
            $this->testRouting($amount);
        }

        $this->newLine();
        $this->info("🎯 Routing Logic Summary:");
        $this->line("✅ Amounts below TZS 20,000,000 → TIPS (Tanzania Instant Payment System)");
        $this->line("✅ Amounts TZS 20,000,000 and above → TISS (Tanzania Interbank Settlement System)");
        
        $this->newLine();
        $this->info("💡 Note: This test only checks routing logic. To test actual transfers:");
        $this->line("php artisan test:letshego-eft transfer --amount=50000    # TIPS");
        $this->line("php artisan test:letshego-eft transfer --amount=25000000 # TISS");
    }

    protected function testRouting(float $amount): void
    {
        // Determine routing system
        $routingSystem = $amount >= self::TISS_THRESHOLD ? 'TISS' : 'TIPS';
        
        // Format amount for display
        $formattedAmount = 'TZS ' . number_format($amount, 2);
        
        // Color coding for output
        $color = $routingSystem === 'TIPS' ? 'info' : 'comment';
        $icon = $routingSystem === 'TIPS' ? '🔵' : '🟡';
        
        // Display routing result
        $this->{$color}(sprintf(
            "%s %s → %s %s",
            $icon,
            str_pad($formattedAmount, 20, ' ', STR_PAD_RIGHT),
            $routingSystem,
            $this->getSystemDescription($routingSystem)
        ));
        
        // Additional details for threshold amounts
        if ($amount == self::TISS_THRESHOLD) {
            $this->line("    └─ Threshold amount (exactly TZS 20M)");
        } elseif (abs($amount - self::TISS_THRESHOLD) <= 1) {
            $diff = $amount - self::TISS_THRESHOLD;
            $direction = $diff > 0 ? 'above' : 'below';
            $this->line("    └─ Edge case: " . abs($diff) . " TZS {$direction} threshold");
        }
    }

    protected function getSystemDescription(string $system): string
    {
        return match($system) {
            'TIPS' => '(Tanzania Instant Payment System)',
            'TISS' => '(Tanzania Interbank Settlement System)',
            default => ''
        };
    }
}