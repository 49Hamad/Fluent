<?php

namespace App\Filament\Resources\ConsultingResource\Pages;

use App\Filament\Resources\ConsultingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsulting extends EditRecord
{
    protected static string $resource = ConsultingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
