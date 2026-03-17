<?php

namespace App\Models;

use App\Traits\AutoGeneratesNumber;
use App\Traits\BelongsToBranch;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ProductionOrder extends Model
{
    use SoftDeletes, AutoGeneratesNumber, BelongsToBranch;

    protected $fillable = [
        'number', 'bill_of_material_id', 'branch_id', 'project_id', 'quantity_to_produce',
        'quantity_produced', 'start_date', 'end_date', 'status', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function getNumberPrefix(): string
    {
        return 'PROD';
    }

    public function bom()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bill_of_material_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function completeProduction()
    {
        if ($this->status === 'completed') return;

        DB::transaction(function () {
            $inventoryService = new InventoryService();
            $bom = $this->bom;

            // 1. Consume Raw Materials
            foreach ($bom->items as $item) {
                $requiredQty = $item->quantity * $this->quantity_to_produce;
                $inventoryService->recordMovement(
                    $item->product_id,
                    'out',
                    $requiredQty,
                    "Production #{$this->number} Consumption",
                    $this->branch_id,
                    0,
                    $this->project_id
                );
            }

            // 2. Add Finished Good
            $inventoryService->recordMovement(
                $bom->product_id,
                'in',
                $this->quantity_to_produce,
                "Production #{$this->number} Finished Good",
                $this->branch_id,
                0,
                $this->project_id
            );

            $this->update([
                'status' => 'completed',
                'quantity_produced' => $this->quantity_to_produce,
                'end_date' => now(),
            ]);
        });

        Notification::make()
            ->title('Production Completed')
            ->success()
            ->body("Production #{$this->number} completed. Stock levels updated.")
            ->send();
    }

    public function checkStockAvailability(): array
    {
        $missing = [];
        $bom = $this->bom;
        if (!$bom) return [];

        foreach ($bom->items as $item) {
            $requiredQty = $item->quantity * $this->quantity_to_produce;
            if ($item->product->stock_quantity < $requiredQty) {
                $missing[] = [
                    'product_name' => $item->product->name,
                    'required' => $requiredQty,
                    'available' => $item->product->stock_quantity,
                ];
            }
        }

        return $missing;
    }
}
