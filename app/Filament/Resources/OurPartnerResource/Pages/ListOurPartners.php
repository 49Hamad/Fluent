<?php

namespace App\Filament\Resources\OurPartnerResource\Pages;

use App\Filament\Resources\OurPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOurPartners extends ListRecords
{
    protected static string $resource = OurPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
