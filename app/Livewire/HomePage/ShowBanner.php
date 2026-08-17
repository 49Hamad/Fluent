<?php

namespace App\Livewire\HomePage;

use App\Models\Hero;
use Livewire\Component;

class ShowBanner extends Component
{
    public function render()
    {
        $Banner = Hero::first();
        return view('livewire.home-page.show-banner',compact('Banner'));
    }
}
