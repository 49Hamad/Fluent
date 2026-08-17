<?php

namespace App\Livewire\HomePage\About;

use App\Models\About;
use Livewire\Component;

class ShowAboutUsPage extends Component
{
    public function render()
    {
        $abouts = About::get();
        return view('livewire.home-page.about.show-about-us-page', compact('abouts'));
    }
}
