<?php

namespace App\Filament\Resources\ContactTextResource\Pages;

use App\Filament\Resources\ContactTextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContactText extends EditRecord
{
    protected static string $resource = ContactTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
