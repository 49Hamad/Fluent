<?php

namespace App\Livewire;

use Exception;
use App\Models\User;
use Livewire\Component;
use App\Models\FormType;
use Livewire\WithFileUploads;
use App\Models\FormEvaluation;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class FormFeedBack extends Component
{

    use WithFileUploads;
    public $tourId;
    public $questions;
    public $responses = [];
    public $tourName;
    public $tourDescription;
    public $tourSection;


    public  $client_name;
    public  $company_name;
    public  $email;
    public  $feedback;
    public  $start_project_date;

    public function mount($id)
{
    $this->tourId = decrypt($id);
    $this->questions = FormType::with(['formSection', 'questions.options'])->findOrFail($this->tourId);
    $this->tourName = $this->questions->title;
    $this->tourDescription = $this->questions->description;
    $this->tourSection = $this->questions->formSection->name;
    // Initialize responses array for each question
    foreach ($this->questions->questions as $question) {
        $this->responses[$question->id] = [
            'number_of_stars' => null, // or 0 if you prefer
            'text' => null,
            'selected_option' => [],
            'image' => null,
        ];
    }
}



public function submitResponses()
{

$rules = [
    'client_name' => 'required|string|min:3|max:50',
    'company_name' => 'required|string|min:3|max:50',
    'email' => 'required|email|max:100',
    'feedback' => 'required|string|min:10|max:200',
    'start_project_date' => 'required|date'
];

foreach ($this->questions->questions as $question) {
    if ($question->is_required) {
        switch ($question->question_type) {
            case 'اجابة قصيرة':
                $rules["responses.{$question->id}.text"] = 'required|string|min:3|max:100';
                break;
            case 'فقرة':
                $rules["responses.{$question->id}.text"] = 'required|string|min:3|max:500';
                break;
            case 'اختيار واحد':
                $rules["responses.{$question->id}.selected_option"] = 'required|string';
                break;
            case 'متعدد الأختيارات':
                $rules["responses.{$question->id}.selected_option"] = 'required|array|min:1';
                break;
            case 'صورة':
                $rules["responses.{$question->id}.image"] = 'required|image';
                break;
            case 'التقييم':
                $rules["responses.{$question->id}.number_of_stars"] = 'required|min:1|max:' . $question->number_of_stars;
                break;
            case 'القائمة المنسدلة':
                $rules["responses.{$question->id}.selected_option"] = 'required|string';
                break;
        }
    }
}



// Perform validation
$this->validate($rules);


    // بدء معاملة
    DB::beginTransaction();


    try {


        // إنشاء تقييم جولة التفتيش
        $inspectionTourEvaluation = FormEvaluation::create([
            'form_type_id' => $this->tourId,
            'client_name' => $this->client_name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'feedback' => $this->feedback,
            'start_project_date' => $this->start_project_date,
            'evaluation_date' => now(),
        ]);

        if($inspectionTourEvaluation)
        {
            // حفظ الاستجابات
            foreach ($this->responses as $questionId => $response) {
                $question = $this->questions->questions->find($questionId);

                // حفظ الاستجابة لكل نوع سؤال
                $inspectionTourEvaluation->responses()->create([
                    'form_evaluation_id' => $inspectionTourEvaluation->id, // تم تصحيح هذا للإشارة إلى التقييم الذي تم إنشاؤه
                    'form_question_id' => $questionId,
                    'response_type' => $question->question_type,
                    'response_text' => $response['text'] ?? null,

                    'response_options' => $question->question_type == 'متعدد الأختيارات'
                    ? $this->getTrueKeysAsJson($response['selected_option'] ?? null)
                    : ( $response['selected_option'] ?  $response['selected_option'] : null),

                    'rating_value' => isset($response['number_of_stars']) ? max($response['number_of_stars']) : null,
                    'type_of_stars' => $question->type_of_stars,
                    'response_image' => isset($response['image']) ? $this->uploadImage($response['image']) : null,
                ]);
            }

            // تأكيد المعاملة
            DB::commit();

            flash()->option('timeout', 10000)->success('تم إرسال الاستجابات بنجاح.');

            Notification::make()
            ->title('لديك تقييم جديدة 😊' . ' ــــ ')
            ->success()
            ->body(' من العميل / ' . ' ـــ ' .$this->client_name)
            ->actions([
                Action::make('markAsRead')
                    ->label('وضع علامة مقروء')
                    ->button()
                    ->markAsRead(),
            ])
            ->sendToDatabase(User::all());

            return redirect()->route('home');

        }

    } catch (Exception $e) {
        // التراجع عن المعاملة إذا حدث خطأ
        DB::rollBack();

        // التعامل مع الخطأ (تسجيله، إظهار رسالة خطأ، إلخ.)
        flash()->option('timeout', 10000)->error('حدث خطأ أثناء إرسال الاستجابات: ' . $e->getMessage());
    }
}


public function getTrueKeysAsJson($response): string {
    if (is_null($response) || !is_array($response)) {
        return json_encode([]); // Return an empty JSON array if input is null or not an array
    }

    $trueKeys = [];
    foreach ($response as $key => $value) {
        if ($value === true) {
            $trueKeys[] = $key;
        }
    }

    return json_encode($trueKeys);
}



    public function toggleRatingValue($questionId, $value)
    {
        // Initialize the number_of_stars array if not set
        if (!isset($this->responses[$questionId]['number_of_stars'])) {
            $this->responses[$questionId]['number_of_stars'] = []; // Initialize as an empty array
        }

        // Get the maximum star value allowed for the question
        $maxStars = $this->questions->number_of_stars; // Ensure this points to the correct property
        // If the value is already selected, deselect all above it
        if (in_array($value, $this->responses[$questionId]['number_of_stars'])) {
            // Deselect all stars greater than the selected one
            $this->responses[$questionId]['number_of_stars'] = array_filter($this->responses[$questionId]['number_of_stars'], function($star) use ($value) {
                return $star <= $value; // Keep only the stars less than or equal to the selected star
            });
        } else {
            // Select all stars from 1 to the selected star
            $this->responses[$questionId]['number_of_stars'] = range(1, $value);
        }
    }





    // Upload image if response_type is image
    public function uploadImage($image)
    {
        return $image->store('inspection-responses', 'public');
    }



    public function render()
    {
        return view('livewire.form-feed-back');
    }
}
