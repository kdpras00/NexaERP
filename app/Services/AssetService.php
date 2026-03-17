<?php

namespace App\Services;

use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class AssetService
{
    /**
     * Run depreciation for all active fixed assets.
     */
    public static function runMonthlyDepreciation(): int
    {
        $assets = FixedAsset::where('status', 'active')
            ->get();

        $count = 0;
        foreach ($assets as $asset) {
            DB::transaction(function () use ($asset, &$count) {
                $monthlyDepreciation = self::calculateMonthlyDepreciation($asset);
                
                if ($monthlyDepreciation > 0) {
                    $expenseAccount = AccountingService::getAccountByCode('6-2100'); // Depreciation Expense
                    $accumAccount = AccountingService::getAccountByCode('1-5200'); // Accumulated Depreciation

                    if ($expenseAccount && $accumAccount) {
                        AccountingService::createJournalEntry(
                            now(),
                            "Monthly Depreciation - {$asset->name} (" . now()->format('M Y') . ")",
                            [
                                ['account_id' => $expenseAccount->id, 'debit' => $monthlyDepreciation, 'credit' => 0],
                                ['account_id' => $accumAccount->id, 'debit' => 0, 'credit' => $monthlyDepreciation],
                            ]
                        );

                        $asset->increment('accumulated_depreciation', $monthlyDepreciation);
                        $count++;
                    }
                }
            });
        }
        return $count;
    }

    protected static function calculateMonthlyDepreciation(FixedAsset $asset): float
    {
        if ($asset->useful_life_years <= 0) return 0;
        
        // Straight line depreciation: (Cost - Salvage) / (Life in Years * 12)
        $monthlyAmount = ($asset->purchase_cost - $asset->salvage_value) / ($asset->useful_life_years * 12);
        
        // Ensure we don't depreciate below book value
        $maxDepreciation = $asset->purchase_cost - $asset->salvage_value - $asset->accumulated_depreciation;
        
        return min($monthlyAmount, $maxDepreciation);
    }
}
