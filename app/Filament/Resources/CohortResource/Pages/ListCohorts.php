<?php

namespace App\Filament\Resources\CohortResource\Pages;

use App\Filament\Resources\CohortResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCohorts extends ListRecords
{
    protected static string $resource = CohortResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('دفعة جديدة')];
    }
}
