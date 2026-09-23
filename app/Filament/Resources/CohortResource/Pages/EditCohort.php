<?php

namespace App\Filament\Resources\CohortResource\Pages;

use App\Filament\Resources\CohortResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCohort extends EditRecord
{
    protected static string $resource = CohortResource::class;

    protected function getHeaderActions(): array
    {
        // A cohort that already has applications cannot be deleted.
        return [DeleteAction::make()->visible(fn () => ! $this->record->applications()->exists())];
    }
}
