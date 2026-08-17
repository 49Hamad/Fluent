<?php

namespace App\Livewire\HomePage;

use App\Models\Achievement;
use Livewire\Component;

class ShowAchievementPage extends Component
{
    public function render()
    {
        $achievement = Achievement::first();
        return view('livewire.home-page.show-achievement-page',compact('achievement'));
    }
}
