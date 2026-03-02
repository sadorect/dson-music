<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PageViewsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Site Traffic — Last 30 Days';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        // Aggregate per day
        $rows = PageView::selectRaw('DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT session_id) as sessions')
            ->lastDays(30)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels   = [];
        $views    = [];
        $sessions = [];

        for ($i = 29; $i >= 0; $i--) {
            $date       = now()->subDays($i)->format('Y-m-d');
            $labels[]   = Carbon::parse($date)->format('M d');
            $views[]    = (int) ($rows->get($date)?->views    ?? 0);
            $sessions[] = (int) ($rows->get($date)?->sessions ?? 0);
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => 'Page Views',
                    'data'            => $views,
                    'borderColor'     => '#728FCE',
                    'backgroundColor' => 'rgba(114,143,206,0.12)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointRadius'     => 3,
                ],
                [
                    'label'           => 'Unique Sessions',
                    'data'            => $sessions,
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointRadius'     => 3,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['precision' => 0],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => true],
            ],
        ];
    }
}
