<?php

namespace App\Filament\Resources\FormTypeResource\Pages;

use Filament\Actions;
use App\Models\FormSection;
use App\Models\FormQuestion;
use App\Models\FormQuestionOption;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\FormTypeResource;

class EditFormType extends EditRecord
{
    protected static string $resource = FormTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get all the FormQuestions associated with this form_type_id
        $FormQuestions = FormQuestion::where('form_type_id', $data['id'])->with('options')->get();

        // Prepare the questions and their options
        $questions = $FormQuestions->map(function ($question) {
            return [
                'id' => $question->id,
                'is_required' => $question->is_required,
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'rating_value' => $question->number_of_stars,
                'rating_type' => $question->type_of_stars,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                    ];
                })->toArray(),
            ];
        });

        return array_merge($data, [
            'questions' => $questions,
        ]);
    }



    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        $record->update($data);

        // Assuming the 'questions' key exists in the $data array
        if (isset($data['questions'])) {
            // Clear existing questions if necessary
            $record->questions()->delete(); // Optional: Remove existing questions if you want to replace them

            // Save questions and options
            foreach ($data['questions'] as $questionData) {
                // Save the question
                $question = FormQuestion::create([
                    'form_type_id' => $record->id, // Link question to the updated form
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'is_required' => $questionData['is_required'],
                    'number_of_stars' => $questionData['rating_value'] ,
                    'type_of_stars' => $questionData['rating_type'] ,
                ]);

                // Save the question options
                if (isset($questionData['options'])) {
                    foreach ($questionData['options'] as $optionData) {
                        if (isset($optionData['option_text']) && $optionData['option_text'] != null) {
                        FormQuestionOption::create([
                            'form_question_id' => $question->id,
                            'option_text' => $optionData['option_text'] ?? null,
                        ]);
                    }

                    }
                }
            }
        }

        return $record; // Return the updated record
    }


}
