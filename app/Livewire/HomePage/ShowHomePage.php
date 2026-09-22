<?php

namespace App\Livewire\HomePage;

use App\Models\Client;
use App\Models\NumberTalk;
use App\Models\OurPartner;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Fluent homepage (redesign).
 *
 * Renders the approved new design with the new public layout.
 * Content that is managed in Filament is read here and passed to the
 * section partials (numbers, partners).
 * Only sections that exist in the approved design are shown publicly;
 * other stored content (e.g. customer feedback, achievements) stays in
 * Filament only and is not displayed.
 * The contact form stays the existing ShowContactUslPage component.
 */
#[Layout('components.layouts.fluent')]
class ShowHomePage extends Component
{
    public function render()
    {
        return view('livewire.home-page.redesign.home', [
            'numberTalk'    => NumberTalk::first(),
            'clients'       => Client::where('is_active', true)->get(),
            'partnersTitle' => OurPartner::first()?->title ?: 'شركاء النجاح',
        ]);
    }
}
