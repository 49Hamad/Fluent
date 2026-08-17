<?php

namespace App\Livewire\HomePage;

use App\Models\Hero;
use App\Models\Consulting;
use Livewire\Component;

class ShowBanner extends Component
{
    public function render()
    {
        $Banner = Hero::first();
        $Consulting = Consulting::first();

        return view('livewire.home-page.show-banner', compact('Banner', 'Consulting'));
    }
}
