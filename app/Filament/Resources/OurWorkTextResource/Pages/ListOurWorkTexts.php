<?php

namespace App\Filament\Resources\OurWorkTextResource\Pages;

use App\Filament\Resources\OurWorkTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOurWorkTexts extends ListRecords
{
    protected static string $resource = OurWorkTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
