<?php

namespace App\Filament\Resources\AgreementResource\Pages;

use App\Filament\Resources\AgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgreement extends EditRecord
{
    protected static string $resource = AgreementResource::class;

    /** A locked (accepted) version: only "active" may change — enforced here, not just in the form. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->isLocked()) {
            return ['is_active' => (bool) ($data['is_active'] ?? false)];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        AgreementResource::deactivateOthers($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            // An accepted version is evidence — it can never be deleted.
            Actions\DeleteAction::make()->visible(fn () => ! $this->record->isLocked()),
        ];
    }
}
