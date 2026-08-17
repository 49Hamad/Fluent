<?php

namespace App\Filament\Resources\OurWorkTextResource\Pages;

use App\Filament\Resources\OurWorkTextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOurWorkText extends CreateRecord
{
    protected static string $resource = OurWorkTextResource::class;
    protected static bool $canCreateAnother = false;
}
