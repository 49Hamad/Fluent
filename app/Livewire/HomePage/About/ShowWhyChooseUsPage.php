<?php

namespace App\Livewire\HomePage\About;

use App\Models\WhyChooseUs;
use App\Models\Consulting;
use Livewire\Component;

class ShowWhyChooseUsPage extends Component
{
    public function render()
    {
        $why_choose_us = WhyChooseUs::first();
        $Consulting = Consulting::first();

        return view('livewire.home-page.about.show-why-choose-us-page', compact('why_choose_us', 'Consulting'));
    }
}
