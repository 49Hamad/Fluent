<?php

namespace App\Livewire\HomePage;

use App\Models\FormEvaluation;
use Livewire\Component;

class ShowTestimonialPage extends Component
{
    public function render()
    {
        $FormEvaluations = FormEvaluation::where('is_active',true)->get();
        return view('livewire.home-page.show-testimonial-page',compact('FormEvaluations'));
    }
}
