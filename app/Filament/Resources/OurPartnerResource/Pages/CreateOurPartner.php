<?php

namespace App\Filament\Resources\OurPartnerResource\Pages;

use App\Filament\Resources\OurPartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOurPartner extends CreateRecord
{
    protected static string $resource = OurPartnerResource::class;
    protected static bool $canCreateAnother = false;
}
