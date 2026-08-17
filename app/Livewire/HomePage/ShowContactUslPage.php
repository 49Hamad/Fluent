<?php

namespace App\Livewire\HomePage;

use App\Models\User;
use App\Models\Contact;
use App\Models\Setting;
use Livewire\Component;
use App\Mail\ContactUsMail;
use App\Models\ContactText;
use App\Models\ExtraService;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class ShowContactUslPage extends Component
{

   public $name, $email, $subject, $extra_service, $description;

    public function submit()
    {
        // Validate form fields
        $this->validate([
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|min:8|max:100',
            'subject' => 'required|string|min:3|max:100',
            'extra_service' => 'required|exists:extra_services,name',
            'description' => 'required|string|min:10|max:200',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'extra_services' => $this->extra_service,
            'description' => $this->description,
        ];

        try {
            // Create contact record
            Contact::create($data);

            // Send notification
            Notification::make()
                ->title('لديك رسالة جديدة 😊' . ' ــــ ')
                ->success()
                ->body(' بريد الالكتروني جديد / ' . ' ـــ ' .$this->name)
                ->actions([
                    Action::make('markAsRead')
                        ->label('وضع علامة مقروء')
                        ->button()
                        ->markAsRead(),
                ])
                ->sendToDatabase(User::all());

            // Send email
       Mail::to('fluent@fluent.sa')->send(new ContactUsMail($data));

            flash()->option('timeout', 10000)->success('تم إرسال استفسارك بنجاح!');
            $this->reset(['name', 'email', 'subject', 'extra_service', 'description']);
        } catch (\Exception $th) {
            flash()->error(__('يرجى التأكد من بيانات الإرسال'. $th));
        }
    }
    public function render()
    {
        $Setting = Setting::first();
        $extraServices = ExtraService::where('is_active',true)->get();
        $ContactText = ContactText::first() ?? null;
        return view('livewire.home-page.show-contact-usl-page',compact('Setting','ContactText','extraServices'));
    }
}
