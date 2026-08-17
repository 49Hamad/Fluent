<?php

namespace App\Filament\Resources\NumberTalkResource\Pages;

use App\Filament\Resources\NumberTalkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNumberTalk extends EditRecord
{
    protected static string $resource = NumberTalkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
