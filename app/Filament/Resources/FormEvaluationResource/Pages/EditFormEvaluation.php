<?php

namespace App\Filament\Resources\FormEvaluationResource\Pages;

use Filament\Actions;
use App\Models\FormResponse;
use App\Models\FormEvaluation;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\FormEvaluationResource;

class EditFormEvaluation extends EditRecord
{
    protected static string $resource = FormEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $formResponses = FormResponse::where('form_evaluation_id', $data['id'])
            ->with(['question', 'question.options'])
            ->get();

        $questions = $formResponses->map(function ($response) {
            if ($response->question) {

                // قم بالتحقق من نوع response_options ومعالجته
                $responseOptions = $response->response_options;

                // التأكد من أن القيمة ليست NULL
                if (!is_null($responseOptions)) {
                    // إذا كانت قيمة نصية، قم بإعادة تشكيلها إلى مصفوفة
                    if (is_string($responseOptions)) {
                        // إذا كانت تحتوي على JSON، قم بفك تشفيرها
                        $decodedOptions = json_decode($responseOptions, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $responseOptions = $decodedOptions;
                        } else {
                            // إذا لم تكن JSON، اجعلها مصفوفة تحتوي على هذه القيمة
                            $responseOptions = [$responseOptions];
                        }
                    } elseif (!is_array($responseOptions)) {
                        // إذا كانت قيمة أخرى، قم بإعادة تشكيلها إلى مصفوفة
                        $responseOptions = [(string)$responseOptions];
                    }
                } else {
                    // تعيين قيمة فارغة في حالة عدم وجود استجابة
                    $responseOptions = [];
                }

             

                return [
                    'question_id' => $response->question->id,
                    'is_required' => $response->question->is_required,
                    'question_type' => $response->question->question_type,
                    'question_text' => $response->question->question_text,
                    'rating_value' => $response->rating_value . ' من ' . $response->question->number_of_stars,
                    'type_of_stars' => $response->type_of_stars,
                    'response_text' => $response->response_text,
                    'response_type' => $response->response_type,
                    'response_image' => $response->response_image,
                    'response_options' => $responseOptions,
                    'options' => $response->question->options->map(function ($option) use ($responseOptions) {
                        $isSelected = in_array($option->option_text, (array)$responseOptions);

                        return [
                            'id' => $option->id,
                            'option_text' => $option->option_text,
                            'selected' => $isSelected,
                            'extra_attributes' => [
                                'style' => $isSelected ? 'background-color: #e6f7ff;' : '',
                            ],
                        ];
                    })->toArray(),
                ];
            }
        })->filter();

        return array_merge($data, [
            'questions' => $questions,
        ]);
    }








}

