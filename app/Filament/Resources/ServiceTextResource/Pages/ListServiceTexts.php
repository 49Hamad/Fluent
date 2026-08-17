<?php

namespace App\Filament\Resources\ServiceTextResource\Pages;

use App\Filament\Resources\ServiceTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceTexts extends ListRecords
{
    protected static string $resource = ServiceTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
