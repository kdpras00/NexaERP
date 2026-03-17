<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpportunityResource\Pages\ListOpportunities;
use App\Filament\Resources\OpportunityResource\Pages\CreateOpportunity;
use App\Filament\Resources\OpportunityResource\Pages\EditOpportunity;
use App\Models\Opportunity;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Opportunity Details')
                    ->schema([
                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'company')
                            ->required()->searchable()->preload(),
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\TextInput::make('value')
                            ->numeric()->required()->minValue(1),
                        Forms\Components\Select::make('stage')
                            ->options([
                                'prospecting' => 'Prospecting',
                                'qualification' => 'Qualification',
                                'proposal' => 'Proposal',
                                'negotiation' => 'Negotiation',
                                'closed_won' => 'Closed Won (Deal)',
                                'closed_lost' => 'Closed Lost',
                            ])->required()->default('prospecting'),
                        Forms\Components\DatePicker::make('expected_closed_date'),
                        Forms\Components\TextInput::make('probability')
                            ->label('Probability (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('lead.company')->label('Lead'),
                Tables\Columns\TextColumn::make('value')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'closed_won' => 'success',
                            'closed_lost' => 'danger',
                            'negotiation' => 'warning',
                            'proposal' => 'info',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('probability')->formatStateUsing(fn ($state) => $state . '%')->sortable(),
                Tables\Columns\TextColumn::make('expected_closed_date')->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage'),
            ])
            ->actions([
                Action::make('convertToSO')
                    ->label('Convert to Sales Order')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->visible(fn (Opportunity $record) => $record->stage === 'closed_won')
                    ->requiresConfirmation()
                    ->action(function (Opportunity $record) {
                        // 1. Check if lead has a customer record or create one
                        $lead = $record->lead;
                        $customer = \App\Models\Customer::firstOrCreate(
                            ['email' => $lead->email],
                            [
                                'name' => $lead->contact_person,
                                'company_name' => $lead->company,
                                'phone' => $lead->phone,
                                'address' => $lead->notes,
                            ]
                        );

                        // 2. Create Sales Order
                        $so = SalesOrder::create([
                            'customer_id' => $customer->id,
                            'branch_id' => auth()->user()->branch_id ?? \App\Models\Branch::first()?->id,
                            'date' => now(),
                            'status' => 'pending',
                            'total_amount' => $record->value,
                            'notes' => "Converted from Opportunity: {$record->title}",
                        ]);

                        Notification::make()
                            ->title('Sales Order Created')
                            ->body("Opportunity converted to SO #{$so->number}")
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
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }
}
