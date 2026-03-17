<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::first();
        $pcsUnit = Unit::where('abbreviation', 'pcs')->first();
        $setUnit = Unit::where('abbreviation', 'set')->first();
        $boxUnit = Unit::where('abbreviation', 'box')->first();
        $unitUnit = Unit::where('abbreviation', 'unit')->first();
        $packUnit = Unit::where('abbreviation', 'pack')->first();

        $electronics = Category::where('name', 'Electronics')->first();
        $furniture = Category::where('name', 'Furniture')->first();
        $supplies = Category::where('name', 'Office Supplies')->first();
        $software = Category::where('name', 'Software Licenses')->first();
        $spareParts = Category::where('name', 'Spare Parts')->first();

        if (!$warehouse) return;

        $products = [
            ['code' => 'PROD-001', 'name' => 'Laptop ASUS ROG Strix', 'category_id' => $electronics?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 15000000, 'selling_price' => 20000000, 'stock_quantity' => 25, 'min_stock' => 5, 'warehouse_id' => $warehouse->id, 'description' => 'High-performance gaming laptop.'],
            ['code' => 'PROD-002', 'name' => 'Monitor LG 27" 4K', 'category_id' => $electronics?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 4500000, 'selling_price' => 6000000, 'stock_quantity' => 40, 'min_stock' => 10, 'warehouse_id' => $warehouse->id, 'description' => 'Ultra-sharp 4K IPS display.'],
            ['code' => 'PROD-003', 'name' => 'Keyboard Mechanical Logitech', 'category_id' => $electronics?->id, 'unit_id' => $pcsUnit?->id, 'cost_price' => 800000, 'selling_price' => 1200000, 'stock_quantity' => 100, 'min_stock' => 20, 'warehouse_id' => $warehouse->id, 'description' => 'RGB mechanical keyboard.'],
            ['code' => 'PROD-004', 'name' => 'Mouse Wireless Logitech MX', 'category_id' => $electronics?->id, 'unit_id' => $pcsUnit?->id, 'cost_price' => 650000, 'selling_price' => 950000, 'stock_quantity' => 80, 'min_stock' => 15, 'warehouse_id' => $warehouse->id, 'description' => 'Ergonomic wireless mouse.'],
            ['code' => 'PROD-005', 'name' => 'Printer Epson L3210', 'category_id' => $electronics?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 2200000, 'selling_price' => 3000000, 'stock_quantity' => 15, 'min_stock' => 5, 'warehouse_id' => $warehouse->id, 'description' => 'All-in-one inkjet printer.'],
            ['code' => 'PROD-006', 'name' => 'Meja Kerja Executive', 'category_id' => $furniture?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 3500000, 'selling_price' => 5000000, 'stock_quantity' => 10, 'min_stock' => 3, 'warehouse_id' => $warehouse->id, 'description' => 'Premium executive office desk.'],
            ['code' => 'PROD-007', 'name' => 'Kursi Ergonomis Premium', 'category_id' => $furniture?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 2800000, 'selling_price' => 4200000, 'stock_quantity' => 20, 'min_stock' => 5, 'warehouse_id' => $warehouse->id, 'description' => 'Ergonomic mesh office chair.'],
            ['code' => 'PROD-008', 'name' => 'Kertas HVS A4 80gsm', 'category_id' => $supplies?->id, 'unit_id' => $boxUnit?->id, 'cost_price' => 45000, 'selling_price' => 55000, 'stock_quantity' => 500, 'min_stock' => 50, 'warehouse_id' => $warehouse->id, 'description' => 'Standard A4 paper, 500 sheets/ream.'],
            ['code' => 'PROD-009', 'name' => 'Tinta Printer Epson 003', 'category_id' => $supplies?->id, 'unit_id' => $pcsUnit?->id, 'cost_price' => 75000, 'selling_price' => 95000, 'stock_quantity' => 200, 'min_stock' => 30, 'warehouse_id' => $warehouse->id, 'description' => 'Refill ink for Epson printers.'],
            ['code' => 'PROD-010', 'name' => 'Microsoft 365 Business', 'category_id' => $software?->id, 'unit_id' => $pcsUnit?->id, 'cost_price' => 1500000, 'selling_price' => 2000000, 'stock_quantity' => 50, 'min_stock' => 10, 'warehouse_id' => $warehouse->id, 'description' => 'Annual business license.'],
            ['code' => 'PROD-011', 'name' => 'UPS APC 1100VA', 'category_id' => $electronics?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 1200000, 'selling_price' => 1800000, 'stock_quantity' => 30, 'min_stock' => 8, 'warehouse_id' => $warehouse->id, 'description' => 'Uninterruptible power supply.'],
            ['code' => 'PROD-012', 'name' => 'Kabel LAN Cat6 (305m)', 'category_id' => $spareParts?->id, 'unit_id' => $boxUnit?->id, 'cost_price' => 850000, 'selling_price' => 1100000, 'stock_quantity' => 8, 'min_stock' => 3, 'warehouse_id' => $warehouse->id, 'description' => 'Cat6 UTP cable, 305m roll.'],
            ['code' => 'PROD-013', 'name' => 'Webcam Logitech C920', 'category_id' => $electronics?->id, 'unit_id' => $pcsUnit?->id, 'cost_price' => 900000, 'selling_price' => 1300000, 'stock_quantity' => 35, 'min_stock' => 10, 'warehouse_id' => $warehouse->id, 'description' => 'HD webcam for video conferencing.'],
            ['code' => 'PROD-014', 'name' => 'Rak Besi 5 Tingkat', 'category_id' => $furniture?->id, 'unit_id' => $unitUnit?->id, 'cost_price' => 750000, 'selling_price' => 1100000, 'stock_quantity' => 12, 'min_stock' => 3, 'warehouse_id' => $warehouse->id, 'description' => 'Heavy-duty steel shelving.'],
            ['code' => 'PROD-015', 'name' => 'Alat Tulis Set', 'category_id' => $supplies?->id, 'unit_id' => $setUnit?->id, 'cost_price' => 35000, 'selling_price' => 50000, 'stock_quantity' => 300, 'min_stock' => 50, 'warehouse_id' => $warehouse->id, 'description' => 'Stationery set: pen, pencil, eraser, ruler.'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['code' => $product['code']], $product);
        }
    }
}
