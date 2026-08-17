<?php

namespace App\Filament\Resources\FormEvaluationResource\Pages;

use App\Filament\Resources\FormEvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormEvaluations extends ListRecords
{
    protected static string $resource = FormEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
