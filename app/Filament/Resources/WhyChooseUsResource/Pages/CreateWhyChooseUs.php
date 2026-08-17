<?php

namespace App\Filament\Resources\WhyChooseUsResource\Pages;

use App\Filament\Resources\WhyChooseUsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWhyChooseUs extends CreateRecord
{
    protected static string $resource = WhyChooseUsResource::class;
    protected static bool $canCreateAnother = false;
}
