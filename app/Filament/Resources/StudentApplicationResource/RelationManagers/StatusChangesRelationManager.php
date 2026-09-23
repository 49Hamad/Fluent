<?php

namespace App\Filament\Resources\StudentApplicationResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Read-only status history on the application page. */
class StatusChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusChanges';

    protected static ?string $title = 'سجل الحالة';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'سجل الحالة';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('from_status')->label('من')->badge()->placeholder('—'),
                Tables\Columns\TextColumn::make('to_status')->label('إلى')->badge(),
                Tables\Columns\TextColumn::make('changedBy.name')->label('بواسطة')->placeholder('النموذج العام'),
                Tables\Columns\IconColumn::make('student_notified')->label('أُبلغ الطالب بالبريد')->boolean(),
            ]);
    }
}
