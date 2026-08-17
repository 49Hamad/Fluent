<?php

namespace App\Filament\Resources\OurWorkResource\Pages;

use App\Filament\Resources\OurWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOurWork extends EditRecord
{
    protected static string $resource = OurWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
