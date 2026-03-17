<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_role') ?? false;
    }



    protected static function getPermissionGroups(): array
    {
        return [
            'Company' => ['company', 'branch', 'department'],
            'User Management' => ['user', 'role'],
            'CRM' => ['lead', 'opportunity'],
            'Master Data' => ['customer', 'supplier', 'category', 'unit', 'warehouse'],
            'Products & Inventory' => ['product', 'stock_movement', 'stock_adjustment', 'stock_opname'],
            'Sales' => ['quotation', 'sales_order', 'delivery_order', 'sales_invoice'],
            'Purchasing' => ['purchase_request', 'purchase_order', 'goods_receipt', 'purchase_invoice'],
            'Finance' => ['chart_of_account', 'journal_entry', 'cash_transaction', 'accounts_receivable', 'accounts_payable', 'expense', 'expense_category'],
            'HR' => ['employee', 'attendance', 'leave', 'payroll'],
            'Projects' => ['project', 'task'],
            'Assets' => ['fixed_asset', 'asset_maintenance'],
            'Manufacturing' => ['bill_of_material', 'production_order', 'quality_control'],
            'System' => ['activity_log'],
        ];
    }

    protected static function getPagePermissions(): array
    {
        return [
            'Financial Report' => 'page_FinancialReport',
            'Inventory Report' => 'page_InventoryReport',
            'Purchase Report' => 'page_PurchaseReport',
            'Sales Report' => 'page_SalesReport',
            'Project Profitability' => 'page_ProjectReport',
        ];
    }

    protected static function getWidgetPermissions(): array
    {
        return [
            'Stats Overview' => 'widget_StatsOverview',
            'Sales Chart' => 'widget_SalesChart',
            'Recent Transactions' => 'widget_RecentTransactions',
            'Inventory Alerts' => 'widget_InventoryAlerts',
            'Lead Pipeline Chart' => 'widget_LeadPipelineChart',
            'Stock Alert Widget' => 'widget_StockAlertWidget',
        ];
    }

    public static function form(Form $form): Form
    {
        $permissionGroups = static::getPermissionGroups();
        $actions = [
            'view' => 'View',
            'create' => 'Create',
            'update' => 'Edit',
            'delete' => 'Delete'
        ];
        $specialPermissions = [
            'export_data',
            'import_data',
            'manage_settings',
        ];

        $sections = [];

        $sections[] = Forms\Components\Section::make('Role Information')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);

        $groupSchemas = [];

        foreach ($permissionGroups as $groupName => $modules) {
            $checkboxes = [];

            foreach ($modules as $module) {
                $moduleLabel = ucwords(str_replace(['_', '::'], ' ', $module));
                $options = [];

                foreach ($actions as $action => $label) {
                    $permName = "{$action}_{$module}";
                    $perm = Permission::where('name', $permName)->first();
                    if ($perm) {
                        $options[$perm->name] = $label;
                    }
                }

                if (!empty($options)) {
                    $checkboxes[] = Forms\Components\CheckboxList::make("permissions_{$module}")
                        ->label($moduleLabel)
                        ->options($options)
                        ->columns(['default' => 4])
                        ->gridDirection('row')
                        ->bulkToggleable()
                        ->dehydrated(true)
                        ->afterStateHydrated(function ($component, ?Role $record) use ($options, $module) {
                            if (!$record) return;
                            $component->state($record->permissions->pluck('name')->intersect(array_keys($options))->values()->toArray());
                        });
                }
            }

            if (!empty($checkboxes)) {
                $groupSchemas[] = Forms\Components\Section::make($groupName)
                    ->schema($checkboxes)
                    ->collapsible()
                    ->compact();
            }
        }

        // Add Pages Section
        $pagePermissions = static::getPagePermissions();
        $groupSchemas[] = Forms\Components\Section::make('Pages')
            ->schema([
                Forms\Components\CheckboxList::make('permissions_pages')
                    ->label('')
                    ->options($pagePermissions)
                    ->columns(['default' => 2])
                    ->dehydrated(true)
                    ->afterStateHydrated(function ($component, ?Role $record) use ($pagePermissions) {
                        if (!$record) return;
                        $component->state($record->permissions->pluck('name')->intersect(array_values($pagePermissions))->values()->toArray());
                    })
            ])
            ->collapsible()
            ->compact();

        // Add Widgets Section
        $widgetPermissions = static::getWidgetPermissions();
        $groupSchemas[] = Forms\Components\Section::make('Widgets')
            ->schema([
                Forms\Components\CheckboxList::make('permissions_widgets')
                    ->label('')
                    ->options($widgetPermissions)
                    ->columns(2)
                    ->dehydrated(true)
                    ->afterStateHydrated(function ($component, ?Role $record) use ($widgetPermissions) {
                        if (!$record) return;
                        $component->state($record->permissions->pluck('name')->intersect(array_values($widgetPermissions))->values()->toArray());
                    })
            ])
            ->collapsible()
            ->compact();

        $specialOptions = [];
        foreach ($specialPermissions as $perm) {
            $permModel = Permission::where('name', $perm)->first();
            if ($permModel) {
                $specialOptions[$permModel->name] = ucwords(str_replace('_', ' ', $perm));
            }
        }

        if (!empty($specialOptions)) {
            $groupSchemas[] = Forms\Components\Section::make('Special Permissions')
                ->schema([
                    Forms\Components\CheckboxList::make('permissions_special')
                        ->label('Special Permissions')
                        ->options($specialOptions)
                        ->columns(['default' => 3])
                        ->bulkToggleable()
                        ->dehydrated(true)
                        ->afterStateHydrated(function ($component, ?Role $record) use ($specialOptions) {
                            if (!$record) return;
                            $component->state($record->permissions->pluck('name')->intersect(array_keys($specialOptions))->values()->toArray());
                        }),
                ])
                ->collapsible()
                ->compact();
        }

        $sections[] = Forms\Components\Section::make('Permissions')
            ->description('Select which permissions this role should have. Permissions are grouped by module.')
            ->schema($groupSchemas)
            ->columnSpanFull();

        return $form->schema($sections);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Role $record) {
                        if ($record->name === 'Super Admin') {
                            throw new \Exception('Cannot delete Super Admin role.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
