<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generatePayroll')
                ->label('Bulk Generate')
                ->icon('heroicon-o-cpu-chip')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('month')
                        ->placeholder('MM-YYYY')
                        ->required()
                        ->default(now()->format('m-Y')),
                ])
                ->action(function (array $data) {
                    $employees = \App\Models\Employee::where('status', 'active')->get();
                    $count = 0;
                    foreach ($employees as $employee) {
                        $exists = \App\Models\Payroll::where('employee_id', $employee->id)
                            ->where('month', $data['month'])
                            ->exists();
                        
                        if (!$exists) {
                            \App\Models\Payroll::create([
                                'employee_id' => $employee->id,
                                'month' => $data['month'],
                                'basic_salary' => $employee->salary,
                                'net_salary' => $employee->salary,
                                'status' => 'draft',
                                'pay_date' => now(),
                            ]);
                            $count++;
                        }
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title("Generated $count payroll entries")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
