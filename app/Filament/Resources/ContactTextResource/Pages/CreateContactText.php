<?php

namespace App\Filament\Resources\ContactTextResource\Pages;

use App\Filament\Resources\ContactTextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContactText extends CreateRecord
{
    protected static string $resource = ContactTextResource::class;
    protected static bool $canCreateAnother = false;
}
