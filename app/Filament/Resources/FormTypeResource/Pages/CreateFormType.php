<?php

namespace App\Filament\Resources\FormTypeResource\Pages;

use Filament\Actions;
use App\Models\FormQuestion;
use App\Models\FormQuestionOption;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\FormTypeResource;

class CreateFormType extends CreateRecord
{
    protected static string $resource = FormTypeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the form type first
        $formType = static::getModel()::create($data);

        if (!empty($data['questions']) && is_array($data['questions'])) {
            foreach ($data['questions'] as $questionData) {

                // Create the question
                $question = FormQuestion::create([
                    'form_type_id' => $formType->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'number_of_stars' => $questionData['rating_value'] ?? null,
                    'type_of_stars' => $questionData['rating_type'] ?? null,
                    'is_required' => $questionData['is_required'] ?? false,
                ]);

                // Define question types that require options
                $questionTypesWithOptions = ['اختيار واحد', 'متعدد الأختيارات', 'القائمة المنسدلة','التقييم'];

                // Only create options if the question type requires them
                if (in_array($questionData['question_type'], $questionTypesWithOptions) &&
                    !empty($questionData['options']) && is_array($questionData['options'])) {

                    foreach ($questionData['options'] as $optionData) {
                        // Check if option text is not NULL
                        if (!empty($optionData['option_text'])) {
                            FormQuestionOption::create([
                                'form_question_id' => $question->id,
                                'option_text' => $optionData['option_text']
                            ]);
                        }
                    }
                }
            }
        }

        return $formType; // Return the created form type
    }





}
