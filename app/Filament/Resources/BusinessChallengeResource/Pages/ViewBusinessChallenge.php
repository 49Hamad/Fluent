<?php

namespace App\Filament\Resources\BusinessChallengeResource\Pages;

use App\Enums\ChallengeStatus;
use App\Filament\Resources\BusinessChallengeResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessChallenge extends ViewRecord
{
    protected static string $resource = BusinessChallengeResource::class;

    public function getTitle(): string
    {
        return $this->record->org_name;
    }

    protected function getHeaderActions(): array
    {
        $canUpdate = fn () => auth()->user()?->can('update', $this->record);

        return [
            Actions\Action::make('change_status')
                ->label('تغيير الحالة')
                ->icon('heroicon-o-arrow-path')
                ->visible($canUpdate)
                ->form(fn () => BusinessChallengeResource::statusFormSchema($this->record))
                ->modalHeading('تغيير حالة التحدي')
                ->modalSubmitActionLabel('حفظ الحالة')
                ->action(function (array $data) {
                    BusinessChallengeResource::setStatus($this->record, ChallengeStatus::from($data['status']));
                    $this->record->refresh();
                    Notification::make()->success()->title('تم حفظ الحالة')->send();
                }),

            Actions\Action::make('notes')
                ->label('ملاحظات داخلية')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible($canUpdate)
                ->fillForm(fn () => ['internal_notes' => $this->record->internal_notes])
                ->form([
                    Forms\Components\Textarea::make('internal_notes')->label('الملاحظات')->rows(8)->maxLength(5000)
                        ->helperText('لفريق Fluent فقط — لا تظهر للجهة أبدًا.'),
                ])
                ->modalHeading('ملاحظات داخلية')
                ->action(function (array $data) {
                    abort_unless(auth()->user()?->can('update', $this->record), 403);
                    $this->record->update(['internal_notes' => $data['internal_notes']]);
                    Notification::make()->success()->title('تم حفظ الملاحظات')->send();
                }),

            Actions\Action::make('file')
                ->label('تحميل الملف')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => filled($this->record->file_path))
                ->action(fn () => BusinessChallengeResource::downloadFile($this->record)),

            Actions\DeleteAction::make()
                ->modalDescription('سيُحذف التحدي والملف المرفق نهائيًا. لا يمكن التراجع.'),
        ];
    }
}
