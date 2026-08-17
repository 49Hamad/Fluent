<?php

namespace App\Livewire\HomePage;

use App\Models\User;
use Livewire\Component;
use App\Models\Consulting;
use App\Models\ExtraService;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class ShowCallToActionPage extends Component
{
    public $name;
    public $serviceType;
    public $subject;
    public $message;
    public $showWhatsAppForm = false; // To control form visibility

    public function render()
    {
        $Consulting = Consulting::first();
        $extraServices = ExtraService::where('is_active',true)->get();
        return view('livewire.home-page.show-call-to-action-page', compact('Consulting','extraServices'));
    }

    public function sendWhatsApp()
    {

        // Validate the input data
        $this->validate([
            'name' => 'required|string|max:255',
            'serviceType' => 'required|exists:extra_services,name',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Create the WhatsApp message
        $whatsAppMessage = urlencode("الاسم: {$this->name}\nنوع الخدمة: {$this->serviceType}\nالموضوع: {$this->subject}\nالرسالة: {$this->message}");
        $whatsAppLink = "https://api.whatsapp.com/send?phone=966547291315&text={$whatsAppMessage}";

        // Send notification
        Notification::make()
            ->title('لديك رسالة جديدة 😊' . ' ــــ ')
            ->success()
            ->body('استشارة من الواتس اب جديد / ' . ' ـــ ' . $this->name)
            ->actions([
                Action::make('markAsRead')
                    ->label('وضع علامة مقروء')
                    ->button()
                    ->markAsRead(),
            ])
            ->sendToDatabase(User::all());

        // Reset the input fields
        $this->reset(['name', 'serviceType', 'subject', 'message']);

      

        // Redirect to the WhatsApp link
        return redirect()->to($whatsAppLink);
    }


    public function toggleWhatsAppForm()
    {
        $this->showWhatsAppForm = !$this->showWhatsAppForm; // Toggle form visibility
    }
}
