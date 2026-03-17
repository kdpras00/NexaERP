<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all modules and their CRUD actions
        $modules = [
            // Company Management
            'company', 'branch', 'department',
            // User Management
            'user', 'role',
            // Master Data
            'customer', 'supplier', 'category', 'unit', 'warehouse',
            // Products & Inventory
            'product', 'stock_movement', 'stock_adjustment', 'stock_opname',
            // Sales
            'quotation', 'sales_order', 'delivery_order', 'sales_invoice',
            // Purchasing
            'purchase_request', 'purchase_order', 'goods_receipt', 'purchase_invoice',
            // Finance
            'chart_of_account', 'journal_entry', 'cash_transaction',
            'accounts_receivable', 'accounts_payable',
            // HR
            'employee', 'attendance', 'leave', 'payroll',
            // Projects
            'project', 'task',
            // Assets
            'asset', 'asset_maintenance',
            // Reports
            'report_sales', 'report_purchase', 'report_inventory', 'report_financial',
            // Activity Log
            'activity_log',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        // Create all permissions
        $allPermissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permName = "{$action}_{$module}";
                Permission::firstOrCreate(['name' => $permName]);
                $allPermissions[] = $permName;
            }
        }

        // Additional special permissions
        $specialPermissions = [
            'approve_purchase_request',
            'approve_journal_entry',
            'export_data',
            'import_data',
            'view_dashboard',
            'manage_settings',
        ];
        foreach ($specialPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $allPermissions[] = $perm;
        }

        // Create roles and assign permissions
        // 1. Super Admin - gets all permissions via Gate::before, but assign all for reference
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions($allPermissions);

        // 2. Admin - everything except system settings
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $adminPerms = array_filter($allPermissions, fn($p) => $p !== 'manage_settings');
        $admin->syncPermissions($adminPerms);

        // 3. Sales Manager
        $salesManager = Role::firstOrCreate(['name' => 'Sales Manager']);
        $salesManager->syncPermissions([
            // Sales full access
            'view_quotation', 'create_quotation', 'edit_quotation', 'delete_quotation',
            'view_sales_order', 'create_sales_order', 'edit_sales_order', 'delete_sales_order',
            'view_delivery_order', 'create_delivery_order', 'edit_delivery_order', 'delete_delivery_order',
            'view_sales_invoice', 'create_sales_invoice', 'edit_sales_invoice', 'delete_sales_invoice',
            // Customer full access
            'view_customer', 'create_customer', 'edit_customer', 'delete_customer',
            // Product read-only
            'view_product',
            // Stock read-only
            'view_stock_movement',
            // Reports
            'view_report_sales', 'view_report_inventory',
            // AR
            'view_accounts_receivable', 'create_accounts_receivable', 'edit_accounts_receivable',
            // Dashboard
            'view_dashboard',
            'export_data',
        ]);

        // 4. Purchase Manager
        $purchaseManager = Role::firstOrCreate(['name' => 'Purchase Manager']);
        $purchaseManager->syncPermissions([
            // Purchase full access
            'view_purchase_request', 'create_purchase_request', 'edit_purchase_request', 'delete_purchase_request',
            'approve_purchase_request',
            'view_purchase_order', 'create_purchase_order', 'edit_purchase_order', 'delete_purchase_order',
            'view_goods_receipt', 'create_goods_receipt', 'edit_goods_receipt', 'delete_goods_receipt',
            'view_purchase_invoice', 'create_purchase_invoice', 'edit_purchase_invoice', 'delete_purchase_invoice',
            // Supplier full access
            'view_supplier', 'create_supplier', 'edit_supplier', 'delete_supplier',
            // Product read-only
            'view_product',
            // Stock read-only
            'view_stock_movement',
            // Reports
            'view_report_purchase', 'view_report_inventory',
            // AP
            'view_accounts_payable', 'create_accounts_payable', 'edit_accounts_payable',
            // Dashboard
            'view_dashboard',
            'export_data',
        ]);

        // 5. Accountant
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->syncPermissions([
            // Finance full access
            'view_chart_of_account', 'create_chart_of_account', 'edit_chart_of_account', 'delete_chart_of_account',
            'view_journal_entry', 'create_journal_entry', 'edit_journal_entry', 'delete_journal_entry',
            'approve_journal_entry',
            'view_cash_transaction', 'create_cash_transaction', 'edit_cash_transaction', 'delete_cash_transaction',
            'view_accounts_receivable', 'create_accounts_receivable', 'edit_accounts_receivable', 'delete_accounts_receivable',
            'view_accounts_payable', 'create_accounts_payable', 'edit_accounts_payable', 'delete_accounts_payable',
            // Invoice read-only
            'view_sales_invoice', 'view_purchase_invoice',
            // Reports
            'view_report_sales', 'view_report_purchase', 'view_report_financial',
            // Dashboard
            'view_dashboard',
            'export_data',
        ]);

        // 6. HR Manager
        $hrManager = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrManager->syncPermissions([
            // HR full access
            'view_employee', 'create_employee', 'edit_employee', 'delete_employee',
            'view_attendance', 'create_attendance', 'edit_attendance', 'delete_attendance',
            'view_leave', 'create_leave', 'edit_leave', 'delete_leave',
            'view_payroll', 'create_payroll', 'edit_payroll', 'delete_payroll',
            // Department read
            'view_department',
            'view_branch',
            // Dashboard
            'view_dashboard',
            'export_data',
        ]);

        // 7. Warehouse Staff
        $warehouseStaff = Role::firstOrCreate(['name' => 'Warehouse Staff']);
        $warehouseStaff->syncPermissions([
            // Inventory full access
            'view_product', 'edit_product',
            'view_stock_movement', 'create_stock_movement',
            'view_stock_adjustment', 'create_stock_adjustment', 'edit_stock_adjustment',
            'view_stock_opname', 'create_stock_opname', 'edit_stock_opname',
            'view_warehouse',
            // Delivery Orders
            'view_delivery_order', 'edit_delivery_order',
            // Goods Receipt
            'view_goods_receipt', 'edit_goods_receipt',
            // Reports
            'view_report_inventory',
            // Dashboard
            'view_dashboard',
        ]);

        // 8. Project Manager (new role)
        $projectManager = Role::firstOrCreate(['name' => 'Project Manager']);
        $projectManager->syncPermissions([
            'view_project', 'create_project', 'edit_project', 'delete_project',
            'view_task', 'create_task', 'edit_task', 'delete_task',
            'view_employee',
            'view_dashboard',
        ]);

        // 9. Viewer (read-only access)
        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewer->syncPermissions([
            'view_dashboard',
            'view_product', 'view_customer', 'view_supplier',
            'view_sales_order', 'view_purchase_order',
            'view_sales_invoice', 'view_purchase_invoice',
            'view_report_sales', 'view_report_purchase',
            'view_report_inventory', 'view_report_financial',
        ]);
    }
}
