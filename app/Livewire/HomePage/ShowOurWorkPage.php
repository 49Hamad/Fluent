<?php

namespace App\Livewire\HomePage;

use App\Models\OurWork;
use Livewire\Component;
use App\Models\OurWorkText;

class ShowOurWorkPage extends Component
{
    public function render()
    {
        $OurWorks = OurWork::all();
        $OurWorkText = OurWorkText::first() ;
        return view('livewire.home-page.show-our-work-page',compact('OurWorks','OurWorkText'));
    }
}
