<?php

namespace App\Filament\Resources\NumberTalkResource\Pages;

use App\Filament\Resources\NumberTalkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNumberTalks extends ListRecords
{
    protected static string $resource = NumberTalkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
