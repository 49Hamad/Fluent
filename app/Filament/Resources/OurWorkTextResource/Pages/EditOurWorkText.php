<?php

namespace App\Filament\Resources\OurWorkTextResource\Pages;

use App\Filament\Resources\OurWorkTextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOurWorkText extends EditRecord
{
    protected static string $resource = OurWorkTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
