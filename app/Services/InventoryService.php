<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record a stock movement and update product stock quantity.
     */
    public static function recordMovement(int $productId, float $quantity, string $type, string $reference, ?int $warehouseId = null, float $unitCost = 0, ?int $projectId = null): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId ?? $product->warehouse_id,
            'branch_id' => $product->branch_id,
            'project_id' => $projectId,
            'type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost ?: ($type === 'in' ? $product->cost_price : self::calculateAverageCost($productId)),
            'reference' => $reference,
            'date' => now(),
        ]);

        if ($type === 'in') {
            $product->increment('stock_quantity', $quantity);
        } else {
            $product->decrement('stock_quantity', $quantity);
        }
    }

    /**
     * Calculate COGS for a given quantity of a product based on Average Cost.
     */
    public static function calculateCOGS(int $productId, float $quantity): float
    {
        return self::calculateAverageCost($productId) * $quantity;
    }

    public static function calculateAverageCost(int $productId): float
    {
        $data = StockMovement::where('product_id', $productId)
            ->where('type', 'in')
            ->select(DB::raw('SUM(quantity * unit_cost) as total_value'), DB::raw('SUM(quantity) as total_qty'))
            ->first();

        if (!$data || $data->total_qty <= 0) {
            $product = Product::find($productId);
            return $product ? (float)$product->cost_price : 0;
        }

        return $data->total_value / $data->total_qty;
    }
}
