<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Filament\Resources\QuotationResource\RelationManagers;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_quotation') ?? false;
    }
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quotation Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->disabled()->dehydrated(false)->placeholder('Auto-generated'),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()->searchable()->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->required()->default(now()),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Valid Until')
                            ->default(now()->addDays(30)),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                                'converted' => 'Converted to SO',
                                'expired' => 'Expired',
                            ])
                            ->default('draft')->required(),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()->prefix('Rp')->default(0)->disabled()->dehydrated(),
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
                Tables\Columns\TextColumn::make('number')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('customer.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('valid_until')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'converted' => 'warning',
                        'expired' => 'gray',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('total_amount')->money('idr')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted',
                        'rejected' => 'Rejected', 'converted' => 'Converted', 'expired' => 'Expired',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('convertToSO')
                    ->label('Convert to SO')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Quotation $record) => $record->status === 'accepted')
                    ->requiresConfirmation()
                    ->modalHeading('Convert Quotation to Sales Order')
                    ->modalDescription('This will create a new Sales Order with all items from this quotation.')
                    ->action(function (Quotation $record) {
                        $so = SalesOrder::create([
                            'customer_id' => $record->customer_id,
                            'quotation_id' => $record->id,
                            'date' => now(),
                            'status' => 'draft',
                            'total' => $record->total_amount,
                        ]);
                        foreach ($record->items as $item) {
                            $so->items()->create([
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->total,
                            ]);
                        }
                        $record->update(['status' => 'converted']);

                        Notification::make()
                            ->title('Converted to Sales Order')
                            ->body("SO #{$so->number} has been created.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'view' => Pages\ViewQuotation::route('/{record}'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
