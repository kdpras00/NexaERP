<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Filament\Resources\JournalEntryResource\RelationManagers;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;
    protected static ?string $navigationGroup = 'Finance & Accounting';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_journal_entry') ?? false;
    }
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Journal Entry')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->disabled()->dehydrated(false)->placeholder('Auto-generated'),
                    Forms\Components\DatePicker::make('date')
                        ->required()->default(now()),
                    Forms\Components\Select::make('branch_id')
                        ->relationship('branch', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()->preload()->label('Project / Job'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'posted' => 'Posted',
                            'reversed' => 'Reversed',
                        ])->default('draft')->required(),
                    Forms\Components\Textarea::make('description')
                        ->required()->rows(3)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('date')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        'reversed' => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'posted' => 'Posted', 'reversed' => 'Reversed']),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (JournalEntry $record) => $record->status === 'draft')
                    ->action(function (JournalEntry $record) {
                        $record->update(['status' => 'posted']);
                        Notification::make()
                            ->title('Journal Entry Posted')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'view' => Pages\ViewJournalEntry::route('/{record}'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
