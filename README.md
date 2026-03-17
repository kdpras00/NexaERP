# NexaERP

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12">
    <img src="https://img.shields.io/badge/Filament-3-F59E0B?style=for-the-badge&logo=filament" alt="Filament 3">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2+">
    <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
</p>

## 📋 Tentang NexaERP

**NexaERP** adalah sistem *Enterprise Resource Planning* (ERP) komprehensif yang dibangun menggunakan **Laravel 12** dan **Filament 3**. Sistem ini dirancang untuk mengintegrasikan berbagai aspek operasional bisnis dalam satu platform yang efisien, modern, dan mudah digunakan.

## ✨ Fitur Utama

NexaERP mencakup berbagai modul yang saling terintegrasi untuk mendukung proses bisnis dari hulu ke hilir:

### 📦 Manajemen Inventaris & Gudang
- **Warehouse Management** — Manajemen banyak lokasi gudang.
- **Product Catalog** — Katalog produk dengan kategori dan satuan (Unit).
- **Stock Movement** — Pelacakan histori pergerakan stok secara *real-time*.
- **Stock Opname & Adjustment** — Penyesuaian stok fisik dan sistem.
- **Stock Transfer** — Pemindahan stok antar gudang.
- **Inventory Alerts** — Notifikasi otomatis untuk stok yang menipis.

### 💰 Modul Penjualan & CRM
- **CRM (Leads & Opportunities)** — Manajemen prospek dan peluang penjualan.
- **Quotation** — Pembuatan penawaran harga kepada pelanggan.
- **Sales Order (SO)** — Manajemen pesanan penjualan.
- **Delivery Order (DO)** — Pengiriman barang ke pelanggan.
- **Sales Invoice** — Penagihan penjualan yang terintegrasi dengan akuntansi.

### 🛒 Modul Pembelian
- **Purchase Request (PR)** — Pengajuan kebutuhan barang dari departemen.
- **Purchase Order (PO)** — Pemesanan barang kepada pemasok (Supplier).
- **Goods Receipt** — Penerimaan barang masuk dari pemasok.
- **Purchase Invoice** — Pencatatan tagihan dari pemasok.

### 📊 Keuangan & Akuntansi
- **Chart of Accounts (COA)** — Struktur akun akuntansi yang fleksibel.
- **Journal Entries** — Pencatatan jurnal manual dan otomatis.
- **Cash & Bank Transactions** — Manajemen kas masuk dan keluar.
- **Accounts Payable (AP)** — Manajemen hutang usaha.
- **Accounts Receivable (AR)** — Manajemen piutang usaha.
- **Budgeting & Expenses** — Penganggaran dan pelacakan biaya operasional.
- **Financial Reports** — Laporan Laba Rugi, Neraca, dan Arus Kas.

### 🏭 Manufaktur
- **Bill of Materials (BOM)** — Daftar bahan baku untuk produksi.
- **Production Order** — Perintah produksi barang jadi.
- **Quality Control** — Pemeriksaan kualitas barang hasil produksi.

### 👔 Manajemen SDM (HRM)
- **Employee Management** — Database karyawan lengkap per departemen.
- **Attendance & Leave** — Manajemen absensi dan pengajuan cuti.
- **Payroll System** — Penggajian karyawan yang terintegrasi.

### 🔧 Manajemen Aset & Proyek
- **Fixed Assets** — Pencatatan dan penyusutan aset tetap.
- **Asset Maintenance** — Penjadwalan perawatan aset.
- **Project & Task tracking** — Manajemen proyek dan tugas tim.

## 🛠️ Tech Stack

| Komponen | Teknologi |
|-----------|-----------|
| **Framework Utama** | Laravel 12 |
| **Admin Panel** | Filament 3 |
| **Bahasa Pemrograman** | PHP 8.2+ |
| **Database** | MySQL / PostgreSQL |
| **Frontend logic** | Livewire 3 & Alpine.js |
| **Styling** | Tailwind CSS |
| **Permissions** | Spatie Laravel Permission & Filament Shield |
| **Activity Log** | Spatie Activity Log |
| **Reporting** | DomPDF (PDF) & Maatwebsite Excel (Excel) |

## 🚀 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan NexaERP di lingkungan lokal Anda:

```bash
# 1. Clone repository ini
git clone https://github.com/username/NexaERP.git
cd NexaERP

# 2. Instalasi dependensi PHP & JS
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi Database di file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nexaerp
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan migrasi dan seeder
php artisan migrate --seed

# 6. Build aset frontend
npm run build

# 7. Jalankan server lokal
php artisan serve
```

## 👤 Akun Default (Demo)

Sistem telah dilengkapi dengan data *seeder* untuk pengujian. Berikut adalah beberapa akun default:

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** | `admin@nexaerp.com` | `password` |
| **Accountant** | `budi.santoso@nexaerp.com` | `password` |
| **Sales Manager** | `ahmad.fauzi@nexaerp.com` | `password` |
| **HR Manager** | `dewi.lestari@nexaerp.com` | `password` |

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

---
*Dikembangkan dengan ❤️ menggunakan Laravel & Filament.*

