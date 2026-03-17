<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers;
use App\Models\SalesOrder;
use App\Models\DeliveryOrder;
use App\Models\SalesInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\SalesOrderExporter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_sales_order') ?? false;
    }
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sales Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('SO Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
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
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Select::make('quotation_id')
                            ->relationship('quotation', 'number')
                            ->searchable()
                            ->preload()
                            ->label('From Quotation'),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(2),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
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
                    ->visible(fn (SalesOrder $record) => $record->status === 'draft')
                    ->action(fn (SalesOrder $record) => $record->update(['status' => 'pending_approval'])),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SalesOrder $record) => $record->status === 'pending_approval' && auth()->user()->hasRole('Admin'))
                    ->action(fn (SalesOrder $record) => $record->update([
                        'status' => 'confirmed', 
                        'approved_by' => auth()->id(),
                        'approved_at' => now()
                    ])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SalesOrder $record) => $record->status === 'pending_approval' && auth()->user()->hasRole('Admin'))
                    ->requiresConfirmation()
                    ->action(fn (SalesOrder $record) => $record->update(['status' => 'draft'])),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('gray')
                    ->action(function (SalesOrder $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sales-order', ['record' => $record])->output();
                        }, "SO-{$record->number}.pdf");
                    }),
                Action::make('createDO')
                    ->label('Create DO')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (SalesOrder $record) => in_array($record->status, ['confirmed', 'processing']))
                    ->requiresConfirmation()
                    ->action(function (SalesOrder $record) {
                        $do = \App\Models\DeliveryOrder::create([
                            'sales_order_id' => $record->id,
                            'date' => now(),
                            'status' => 'pending',
                        ]);
                        foreach ($record->items as $item) {
                            $do->items()->create([
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                            ]);
                        }
                        $record->update(['status' => 'processing']);

                        Notification::make()
                            ->title('Delivery Order Created')
                            ->body("DO #{$do->number} has been created.")
                            ->success()
                            ->send();
                    }),
                Action::make('createInvoice')
                    ->label('Create Invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (SalesOrder $record) => in_array($record->status, ['confirmed', 'processing']))
                    ->requiresConfirmation()
                    ->action(function (SalesOrder $record) {
                        $invoice = \App\Models\SalesInvoice::create([
                            'customer_id' => $record->customer_id,
                            'branch_id' => $record->branch_id,
                            'sales_order_id' => $record->id,
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
                            ->title('Sales Invoice Created')
                            ->body("Invoice #{$invoice->number} created. AR and Journal Entry auto-generated.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(SalesOrderExporter::class),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(SalesOrderExporter::class),
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
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'view' => Pages\ViewSalesOrder::route('/{record}'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
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
