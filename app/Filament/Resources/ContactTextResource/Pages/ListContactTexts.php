<?php

namespace App\Filament\Resources\ContactTextResource\Pages;

use App\Filament\Resources\ContactTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactTexts extends ListRecords
{
    protected static string $resource = ContactTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
