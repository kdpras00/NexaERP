<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class LeadPipelineChart extends ChartWidget
{
    protected static ?string $heading = 'Lead Pipeline';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Leads by Status',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#94a3b8', // new
                        '#38bdf8', // contacted
                        '#4ade80', // qualified
                        '#f87171', // lost
                    ],
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($data)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
