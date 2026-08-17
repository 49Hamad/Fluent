<?php

namespace App\Livewire\HomePage\About;

use App\Models\Client;
use Livewire\Component;
use App\Models\NumberTalk;
use App\Models\OurPartner;

class ShowNumberTalkPage extends Component
{
    public function render()
    {
        $OurPartner = OurPartner::first();
        $numberTalk = NumberTalk::first();
        $clients = Client::where('is_active',true)->get();
        return view('livewire.home-page.about.show-number-talk-page',compact('numberTalk', 'clients','OurPartner'));
    }
}
