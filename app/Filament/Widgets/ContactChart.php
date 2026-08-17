<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\FormType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ContactChart extends ApexChartWidget
{
    use HasWidgetShield;
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'ContactChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'نموذج التواصل';
    protected static ?int $sort = 3;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */


     protected function getFormSchema(): array
{
    return [

        DatePicker::make('date_start'),

        DatePicker::make('date_end')

    ];
}

protected function getOptions(): array
{
    $dateStart = $this->filterFormData['date_start'];
    $dateEnd = $this->filterFormData['date_end'];

    if ($dateStart && $dateEnd) {

        $evaluations = Contact::whereBetween('created_at', [$dateStart, $dateEnd])
            ->select('email', 'created_at')->get();
    } else {

        $evaluations = Contact::select('email', 'created_at')->get();
    }


    $data = $this->prepareChartData($evaluations);

    return [
        'chart' => [
            'type' => 'line',
            'height' => 300,
        ],
        'series' => [
            [
                'name' => 'ContactChart',
                'data' => $data['counts'], // Use the prepared data
            ],
        ],
        'xaxis' => [
            'categories' => $data['categories'], // Use categories from prepared data
            'labels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ],
        'yaxis' => [
            'labels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ],
        'colors' => ['#ff0045'],
        'stroke' => [
            'curve' => 'smooth',
        ],
    ];
}

private function prepareChartData($evaluations): array
{
    $counts = [];
    $categories = [];

    // Example of categorizing by months
    for ($i = 1; $i <= 12; $i++) {
        $monthName = date('M', mktime(0, 0, 0, $i, 1));
        $categories[] = $monthName;


        $counts[] = $evaluations->filter(function ($evaluation) use ($i) {
            return (int)date('m', strtotime($evaluation->created_at)) === $i;
        })->count();
    }

    return [
        'counts' => $counts,
        'categories' => $categories,
    ];
}



}
