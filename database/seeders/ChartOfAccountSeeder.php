<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ASSETS (1-xxxx)
            ['code' => '1-0000', 'name' => 'Aset', 'type' => 'asset', 'parent_id' => null],
            ['code' => '1-1000', 'name' => 'Kas & Setara Kas', 'type' => 'asset', 'parent_code' => '1-0000'],
            ['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset', 'parent_code' => '1-1000'],
            ['code' => '1-1200', 'name' => 'Bank BCA', 'type' => 'asset', 'parent_code' => '1-1000'],
            ['code' => '1-1300', 'name' => 'Bank Mandiri', 'type' => 'asset', 'parent_code' => '1-1000'],
            ['code' => '1-1400', 'name' => 'Kas Kecil (Petty Cash)', 'type' => 'asset', 'parent_code' => '1-1000'],
            ['code' => '1-2000', 'name' => 'Piutang', 'type' => 'asset', 'parent_code' => '1-0000'],
            ['code' => '1-2100', 'name' => 'Piutang Usaha', 'type' => 'asset', 'parent_code' => '1-2000'],
            ['code' => '1-2200', 'name' => 'Piutang Lain-lain', 'type' => 'asset', 'parent_code' => '1-2000'],
            ['code' => '1-3000', 'name' => 'Persediaan', 'type' => 'asset', 'parent_code' => '1-0000'],
            ['code' => '1-3100', 'name' => 'Persediaan Barang Dagang', 'type' => 'asset', 'parent_code' => '1-3000'],
            ['code' => '1-3200', 'name' => 'Persediaan Bahan Baku', 'type' => 'asset', 'parent_code' => '1-3000'],
            ['code' => '1-4000', 'name' => 'Biaya Dibayar di Muka', 'type' => 'asset', 'parent_code' => '1-0000'],
            ['code' => '1-5000', 'name' => 'Aset Tetap', 'type' => 'asset', 'parent_code' => '1-0000'],
            ['code' => '1-5100', 'name' => 'Tanah', 'type' => 'asset', 'parent_code' => '1-5000'],
            ['code' => '1-5200', 'name' => 'Bangunan', 'type' => 'asset', 'parent_code' => '1-5000'],
            ['code' => '1-5300', 'name' => 'Kendaraan', 'type' => 'asset', 'parent_code' => '1-5000'],
            ['code' => '1-5400', 'name' => 'Peralatan Kantor', 'type' => 'asset', 'parent_code' => '1-5000'],
            ['code' => '1-5900', 'name' => 'Akumulasi Penyusutan', 'type' => 'asset', 'parent_code' => '1-5000'],

            // LIABILITIES (2-xxxx)
            ['code' => '2-0000', 'name' => 'Kewajiban', 'type' => 'liability', 'parent_id' => null],
            ['code' => '2-1000', 'name' => 'Kewajiban Jangka Pendek', 'type' => 'liability', 'parent_code' => '2-0000'],
            ['code' => '2-1100', 'name' => 'Hutang Usaha', 'type' => 'liability', 'parent_code' => '2-1000'],
            ['code' => '2-1200', 'name' => 'Hutang Pajak', 'type' => 'liability', 'parent_code' => '2-1000'],
            ['code' => '2-1300', 'name' => 'Hutang Gaji', 'type' => 'liability', 'parent_code' => '2-1000'],
            ['code' => '2-1400', 'name' => 'Pendapatan Diterima di Muka', 'type' => 'liability', 'parent_code' => '2-1000'],
            ['code' => '2-2000', 'name' => 'Kewajiban Jangka Panjang', 'type' => 'liability', 'parent_code' => '2-0000'],
            ['code' => '2-2100', 'name' => 'Hutang Bank', 'type' => 'liability', 'parent_code' => '2-2000'],

            // EQUITY (3-xxxx)
            ['code' => '3-0000', 'name' => 'Ekuitas', 'type' => 'equity', 'parent_id' => null],
            ['code' => '3-1000', 'name' => 'Modal Disetor', 'type' => 'equity', 'parent_code' => '3-0000'],
            ['code' => '3-2000', 'name' => 'Laba Ditahan', 'type' => 'equity', 'parent_code' => '3-0000'],
            ['code' => '3-3000', 'name' => 'Laba Tahun Berjalan', 'type' => 'equity', 'parent_code' => '3-0000'],

            // REVENUE (4-xxxx)
            ['code' => '4-0000', 'name' => 'Pendapatan', 'type' => 'revenue', 'parent_id' => null],
            ['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'parent_code' => '4-0000'],
            ['code' => '4-1100', 'name' => 'Penjualan Barang', 'type' => 'revenue', 'parent_code' => '4-1000'],
            ['code' => '4-1200', 'name' => 'Penjualan Jasa', 'type' => 'revenue', 'parent_code' => '4-1000'],
            ['code' => '4-2000', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'parent_code' => '4-0000'],
            ['code' => '4-2100', 'name' => 'Pendapatan Bunga', 'type' => 'revenue', 'parent_code' => '4-2000'],
            ['code' => '4-9000', 'name' => 'Diskon Penjualan', 'type' => 'revenue', 'parent_code' => '4-0000'],
            ['code' => '4-9100', 'name' => 'Retur Penjualan', 'type' => 'revenue', 'parent_code' => '4-0000'],

            // EXPENSES (5-xxxx)
            ['code' => '5-0000', 'name' => 'Beban', 'type' => 'expense', 'parent_id' => null],
            ['code' => '5-1000', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'parent_code' => '5-0000'],
            ['code' => '5-1100', 'name' => 'HPP Barang', 'type' => 'expense', 'parent_code' => '5-1000'],
            ['code' => '5-2000', 'name' => 'Beban Operasional', 'type' => 'expense', 'parent_code' => '5-0000'],
            ['code' => '5-2100', 'name' => 'Beban Gaji & Upah', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2200', 'name' => 'Beban Sewa', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2300', 'name' => 'Beban Listrik & Air', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2400', 'name' => 'Beban Telepon & Internet', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2500', 'name' => 'Beban Transportasi', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2600', 'name' => 'Beban Perlengkapan Kantor', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2700', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-2800', 'name' => 'Beban Pemeliharaan', 'type' => 'expense', 'parent_code' => '5-2000'],
            ['code' => '5-3000', 'name' => 'Beban Lain-lain', 'type' => 'expense', 'parent_code' => '5-0000'],
            ['code' => '5-3100', 'name' => 'Beban Administrasi Bank', 'type' => 'expense', 'parent_code' => '5-3000'],
            ['code' => '5-3200', 'name' => 'Beban Pajak', 'type' => 'expense', 'parent_code' => '5-3000'],
        ];

        // First pass: create all accounts without parent
        $codeToIdMap = [];
        foreach ($accounts as $account) {
            $created = ChartOfAccount::create([
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'parent_id' => null,
            ]);
            $codeToIdMap[$account['code']] = $created->id;
        }

        // Second pass: set parent IDs
        foreach ($accounts as $account) {
            if (isset($account['parent_code']) && isset($codeToIdMap[$account['parent_code']])) {
                ChartOfAccount::where('code', $account['code'])
                    ->update(['parent_id' => $codeToIdMap[$account['parent_code']]]);
            }
        }
    }
}
