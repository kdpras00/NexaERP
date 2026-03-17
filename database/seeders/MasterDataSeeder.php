<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            'Electronics', 'Furniture', 'Office Supplies', 'Software Licenses',
            'Services', 'Raw Materials', 'Spare Parts', 'Consumables',
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        // Units
        $units = [
            ['name' => 'Pieces', 'abbreviation' => 'pcs'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Meter', 'abbreviation' => 'm'],
            ['name' => 'Hour', 'abbreviation' => 'hr'],
            ['name' => 'Liter', 'abbreviation' => 'ltr'],
            ['name' => 'Set', 'abbreviation' => 'set'],
            ['name' => 'Unit', 'abbreviation' => 'unit'],
            ['name' => 'Roll', 'abbreviation' => 'roll'],
            ['name' => 'Pack', 'abbreviation' => 'pack'],
        ];
        foreach ($units as $unit) {
            Unit::firstOrCreate(['abbreviation' => $unit['abbreviation']], $unit);
        }

        // Warehouses
        $branch = Branch::first();
        if ($branch) {
            $warehouses = [
                ['name' => 'Gudang Utama', 'location' => 'Gedung A - Lt. 1', 'branch_id' => $branch->id],
                ['name' => 'Gudang B', 'location' => 'Gedung B - Lt. 1', 'branch_id' => $branch->id],
                ['name' => 'Gudang Spare Part', 'location' => 'Gedung C', 'branch_id' => $branch->id],
            ];
            foreach ($warehouses as $wh) {
                Warehouse::firstOrCreate(['name' => $wh['name']], $wh);
            }
        }

        // Customers
        $customers = [
            ['code' => 'CUST-001', 'name' => 'PT Maju Bersama', 'email' => 'info@majubersama.co.id', 'phone' => '021-5551234', 'address' => 'Jl. Sudirman No. 123, Jakarta Selatan', 'tax_number' => '01.234.567.8-012.000'],
            ['code' => 'CUST-002', 'name' => 'CV Teknologi Nusantara', 'email' => 'sales@teknusa.co.id', 'phone' => '021-5552345', 'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan', 'tax_number' => '02.345.678.9-013.000'],
            ['code' => 'CUST-003', 'name' => 'PT Abadi Sentosa', 'email' => 'procurement@abadisentosa.com', 'phone' => '021-5553456', 'address' => 'Jl. HR Rasuna Said Kav. 10, Jakarta Selatan'],
            ['code' => 'CUST-004', 'name' => 'PT Karya Digital', 'email' => 'order@karyadigital.id', 'phone' => '031-8881234', 'address' => 'Jl. Pemuda No. 78, Surabaya'],
            ['code' => 'CUST-005', 'name' => 'CV Sumber Makmur', 'email' => 'info@sumbermakmur.co.id', 'phone' => '022-7771234', 'address' => 'Jl. Asia Afrika No. 56, Bandung'],
            ['code' => 'CUST-006', 'name' => 'PT Global Pratama', 'email' => 'contact@globalpratama.com', 'phone' => '021-5554567', 'address' => 'Jl. Kuningan No. 90, Jakarta Selatan'],
            ['code' => 'CUST-007', 'name' => 'PT Indo Solutions', 'email' => 'sales@indosolutions.co.id', 'phone' => '021-5555678', 'address' => 'Jl. TB Simatupang No. 15, Jakarta Selatan'],
            ['code' => 'CUST-008', 'name' => 'CV Kreatif Media', 'email' => 'info@kreatifmedia.id', 'phone' => '024-3331234', 'address' => 'Jl. Pandanaran No. 23, Semarang'],
        ];
        foreach ($customers as $c) {
            Customer::firstOrCreate(['code' => $c['code']], $c);
        }

        // Suppliers
        $suppliers = [
            ['code' => 'SUPP-001', 'name' => 'PT Sumber Tekno', 'email' => 'sales@sumbertekno.com', 'phone' => '021-7771234', 'address' => 'Jl. Mangga Dua Raya No. 100, Jakarta Utara'],
            ['code' => 'SUPP-002', 'name' => 'CV Distributor Prima', 'email' => 'order@distprima.co.id', 'phone' => '021-7772345', 'address' => 'Jl. Gunung Sahari No. 50, Jakarta Pusat'],
            ['code' => 'SUPP-003', 'name' => 'PT Perkasa Supply', 'email' => 'info@perkasasupply.com', 'phone' => '031-5551234', 'address' => 'Jl. Rungkut Industri No. 12, Surabaya'],
            ['code' => 'SUPP-004', 'name' => 'PT Material Jaya', 'email' => 'procurement@materialjaya.co.id', 'phone' => '022-4441234', 'address' => 'Jl. Soekarno Hatta No. 567, Bandung'],
            ['code' => 'SUPP-005', 'name' => 'CV Logistik Andalan', 'email' => 'sales@logistikandalan.id', 'phone' => '021-7773456', 'address' => 'Jl. Pluit Raya No. 30, Jakarta Utara'],
            ['code' => 'SUPP-006', 'name' => 'PT Office Pro', 'email' => 'order@officepro.co.id', 'phone' => '021-7774567', 'address' => 'Jl. Kelapa Gading Boulevard No. 20, Jakarta Utara'],
        ];
        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['code' => $s['code']], $s);
        }
    }
}
