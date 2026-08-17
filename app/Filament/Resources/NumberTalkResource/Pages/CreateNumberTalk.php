<?php

namespace App\Filament\Resources\NumberTalkResource\Pages;

use App\Filament\Resources\NumberTalkResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNumberTalk extends CreateRecord
{
    protected static string $resource = NumberTalkResource::class;
    protected static bool $canCreateAnother = false;
}
