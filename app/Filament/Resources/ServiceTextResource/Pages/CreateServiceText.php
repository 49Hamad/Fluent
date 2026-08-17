<?php

namespace App\Filament\Resources\ServiceTextResource\Pages;

use App\Filament\Resources\ServiceTextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceText extends CreateRecord
{
    protected static string $resource = ServiceTextResource::class;
    protected static bool $canCreateAnother = false;
}
