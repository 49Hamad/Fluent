<?php

namespace App\Filament\Resources\StudentApplicationResource\Pages;

use App\Filament\Resources\StudentApplicationResource;
use App\Models\StudentApplication;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentApplication extends ViewRecord
{
    protected static string $resource = StudentApplicationResource::class;

    public function getTitle(): string
    {
        return $this->record->full_name;
    }

    protected function getHeaderActions(): array
    {
        $canUpdate = fn () => auth()->user()?->can('update', $this->record);

        return [
            Actions\Action::make('change_status')
                ->label('تغيير الحالة')
                ->icon('heroicon-o-arrow-path')
                ->visible($canUpdate)
                ->form(fn () => StudentApplicationResource::statusFormSchema($this->record))
                ->modalHeading('تغيير حالة الطلب')
                ->modalSubmitActionLabel('حفظ الحالة')
                ->action(function (array $data) {
                    StudentApplicationResource::applyStatus($this->record, $data);
                    $this->record->refresh();
                }),

            Actions\Action::make('interview')
                ->label('المقابلة')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->visible($canUpdate)
                ->fillForm(fn () => $this->record->only(['interview_at', 'interview_mode', 'interview_location', 'interview_note']))
                ->form([
                    Forms\Components\DateTimePicker::make('interview_at')->label('موعد المقابلة')->seconds(false)->native(false),
                    Forms\Components\Select::make('interview_mode')->label('نوع المقابلة')->options(StudentApplication::INTERVIEW_MODES)->native(false),
                    Forms\Components\TextInput::make('interview_location')->label('المكان أو رابط الاجتماع')->maxLength(500),
                    Forms\Components\Textarea::make('interview_note')->label('ملاحظة للطالب')->rows(3)->maxLength(1000)
                        ->helperText('ستظهر للطالب لاحقًا في مساحته وفي بريد المقابلة.'),
                ])
                ->modalHeading('معلومات المقابلة')
                ->action(function (array $data) {
                    $this->record->update($data);
                    Notification::make()->success()->title('تم حفظ معلومات المقابلة')->send();
                }),

            Actions\Action::make('notes')
                ->label('ملاحظات داخلية')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible($canUpdate)
                ->fillForm(fn () => ['internal_notes' => $this->record->internal_notes])
                ->form([
                    Forms\Components\Textarea::make('internal_notes')->label('الملاحظات')->rows(8)->maxLength(5000)
                        ->helperText('لفريق Fluent فقط — لا تظهر للطالب أبدًا.'),
                ])
                ->modalHeading('ملاحظات داخلية')
                ->action(function (array $data) {
                    $this->record->update($data);
                    Notification::make()->success()->title('تم حفظ الملاحظات')->send();
                }),

            Actions\Action::make('cv')
                ->label('تحميل السيرة الذاتية')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn () => StudentApplicationResource::downloadCv($this->record)),

            Actions\DeleteAction::make()
                ->modalDescription('سيُحذف الطلب وملف السيرة الذاتية نهائيًا. لا يمكن التراجع.'),
        ];
    }
}
