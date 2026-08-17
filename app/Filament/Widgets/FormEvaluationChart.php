<?php

namespace App\Filament\Widgets;

use App\Models\FormType;
use App\Models\FormEvaluation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FormEvaluationChart extends ApexChartWidget
{
    use HasWidgetShield;
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'formEvaluationChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'نماذج التقييم';
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


        Select::make('formType')
        ->options(FormType::pluck('title','id'))
        ->live()
        ->preload()
        ,
        DatePicker::make('date_start'),

        DatePicker::make('date_end')

    ];
}

protected function getOptions(): array
{
    $dateStart = $this->filterFormData['date_start'];
    $dateEnd = $this->filterFormData['date_end'];
    $formType = $this->filterFormData['formType'];

    // Ensure the date range and form type are valid
    if ($dateStart && $dateEnd) {
        // Fetch evaluations within the specified date range and form type
        $evaluations = FormEvaluation::where('form_type_id', $formType)
            ->whereBetween('start_project_date', [$dateStart, $dateEnd]) // Change to start_project_date
            ->select('client_name', 'email', 'start_project_date')
            ->get();
    } else {
        // If no date range, get all evaluations for the form type
        $evaluations = FormEvaluation::where('form_type_id', $formType)
            ->select('client_name', 'email', 'start_project_date')
            ->get();
    }

    // Prepare data for the chart
    $data = $this->prepareChartData($evaluations);

    return [
        'chart' => [
            'type' => 'bar',
            'height' => 300,
        ],
        'series' => [
            [
                'name' => 'FormEvaluationChart',
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
        'colors' => ['#f59e0b'],
        'stroke' => [
            'curve' => 'smooth',
        ],
    ];
}

// Function to prepare data for the chart based on start_project_date
private function prepareChartData($evaluations): array
{
    $counts = [];
    $categories = [];

    // Example of categorizing by months
    for ($i = 1; $i <= 12; $i++) {
        $monthName = date('M', mktime(0, 0, 0, $i, 1));
        $categories[] = $monthName;

        // Count evaluations for each month based on start_project_date
        $counts[] = $evaluations->filter(function ($evaluation) use ($i) {
            return (int)date('m', strtotime($evaluation->start_project_date)) === $i;
        })->count();
    }

    return [
        'counts' => $counts,
        'categories' => $categories,
    ];
}



}
