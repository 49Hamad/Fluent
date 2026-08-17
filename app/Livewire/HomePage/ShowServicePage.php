<?php

namespace App\Livewire\HomePage;

use App\Models\Service;
use Livewire\Component;
use App\Models\ServiceText;

class ShowServicePage extends Component
{
    public function render()
    {
        $services = Service::where('is_active',true)->get();
        $serviceText = ServiceText::first();
        return view('livewire.home-page.show-service-page',compact('services', 'serviceText'));
    }
}
