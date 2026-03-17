<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\PurchaseOrderExporter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationGroup = 'Purchasing';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_purchase_order') ?? false;
    }
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('PO Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('code')->required(),
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('email')->email(),
                                Forms\Components\TextInput::make('phone'),
                                Forms\Components\Textarea::make('address'),
                            ]),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Project / Job'),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Pending Approval',
                                'approved' => 'Approved',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Select::make('purchase_request_id')
                            ->relationship('purchaseRequest', 'number')
                            ->searchable()
                            ->preload()
                            ->label('From Purchase Request'),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(2),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('submit')
                    ->label('Submit for Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (PurchaseOrder $record) => $record->status === 'draft')
                    ->action(fn (PurchaseOrder $record) => $record->update(['status' => 'pending_approval'])),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $record) => $record->status === 'pending_approval' && auth()->user()->hasRole('Admin'))
                    ->action(fn (PurchaseOrder $record) => $record->update([
                        'status' => 'approved', 
                        'approved_by' => auth()->id(),
                        'approved_at' => now()
                    ])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PurchaseOrder $record) => $record->status === 'pending_approval' && auth()->user()->hasRole('Admin'))
                    ->requiresConfirmation()
                    ->action(fn (PurchaseOrder $record) => $record->update(['status' => 'draft'])),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('gray')
                    ->action(function (PurchaseOrder $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.purchase-order', ['record' => $record])->output();
                        }, "PO-{$record->number}.pdf");
                    }),
                Action::make('receiveGoods')
                    ->label('Receive Goods')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (PurchaseOrder $record) => in_array($record->status, ['approved', 'processing']))
                    ->form([
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Destination Warehouse')
                            ->options(\App\Models\Warehouse::pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (PurchaseOrder $record, array $data) {
                        $gr = \App\Models\GoodsReceipt::create([
                            'purchase_order_id' => $record->id,
                            'warehouse_id' => $data['warehouse_id'],
                            'date' => now(),
                            'status' => 'processed', 
                        ]);
                        foreach ($record->items as $item) {
                            $gr->items()->create([
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                            ]);
                            
                            $item->product->increment('stock_quantity', $item->quantity);

                            \App\Models\StockMovement::create([
                                'product_id' => $item->product_id,
                                'warehouse_id' => $data['warehouse_id'],
                                'quantity' => $item->quantity,
                                'unit_cost' => $item->price,
                                'type' => 'in',
                                'reference' => "Goods Receipt #{$gr->number} (from PO #{$record->number})",
                                'date' => now(),
                            ]);
                        }
                        $record->update(['status' => 'processing']);

                        Notification::make()
                            ->title('Goods Received')
                            ->body("GR #{$gr->number} created and stock updated.")
                            ->success()
                            ->send();
                    }),
                Action::make('createInvoice')
                    ->label('Create Invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $record) => in_array($record->status, ['approved', 'processing']))
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $invoice = \App\Models\PurchaseInvoice::create([
                            'supplier_id' => $record->supplier_id,
                            'branch_id' => $record->branch_id,
                            'purchase_order_id' => $record->id,
                            'date' => now(),
                            'due_date' => now()->addDays(30),
                            'subtotal' => $record->subtotal,
                            'tax_rate' => $record->tax_rate,
                            'tax_amount' => $record->tax_amount,
                            'total' => $record->total,
                            'payment_status' => 'unpaid',
                        ]);
                        foreach ($record->items as $item) {
                            $invoice->items()->create([
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->total,
                            ]);
                        }
                        
                        Notification::make()
                            ->title('Purchase Invoice Created')
                            ->body("Invoice #{$invoice->number} created. AP and Journal Entry auto-generated.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(PurchaseOrderExporter::class),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(PurchaseOrderExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
