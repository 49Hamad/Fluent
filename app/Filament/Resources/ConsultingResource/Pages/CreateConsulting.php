<?php

namespace App\Filament\Resources\ConsultingResource\Pages;

use App\Filament\Resources\ConsultingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateConsulting extends CreateRecord
{
    protected static string $resource = ConsultingResource::class;
    protected static bool $canCreateAnother = false;
}
