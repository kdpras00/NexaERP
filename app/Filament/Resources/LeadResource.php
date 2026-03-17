<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lead Info')
                    ->schema([
                        Forms\Components\TextInput::make('company')->required()->maxLength(255),
                        Forms\Components\TextInput::make('contact_person')->required()->maxLength(255),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\Select::make('source')
                            ->options([
                                'website' => 'Website',
                                'referral' => 'Referral',
                                'ads' => 'Advertising',
                                'walk_in' => 'Walk In',
                            ]),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'qualified' => 'Qualified',
                                'lost' => 'Lost',
                            ])->default('new')->required(),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedUser', 'name')
                            ->searchable()->preload(),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('contact_person')->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'new' => 'gray',
                            'contacted' => 'info',
                            'qualified' => 'success',
                            'lost' => 'danger',
                            default => 'primary',
                        };
                    }),
                Tables\Columns\TextColumn::make('assignedUser.name')->label('Assigned To'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'lost' => 'Lost',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('convertToOpportunity')
                    ->label('Convert to Opp.')
                    ->icon('heroicon-o-arrow-path-subtitle')
                    ->color('success')
                    ->visible(fn (Lead $record) => $record->status === 'qualified')
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->default(fn (Lead $record) => "Opportunity for {$record->company}"),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\DatePicker::make('expected_closed_date')
                            ->required()
                            ->default(now()->addMonths(1)),
                    ])
                    ->action(function (Lead $record, array $data): void {
                        \App\Models\Opportunity::create([
                            'lead_id' => $record->id,
                            'title' => $data['title'],
                            'value' => $data['value'],
                            'expected_closed_date' => $data['expected_closed_date'],
                            'stage' => 'prospecting',
                            'probability' => 10,
                        ]);

                        $record->update(['status' => 'contacted']); // or keep as qualified

                        \Filament\Notifications\Notification::make()
                            ->title('Converted Successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
