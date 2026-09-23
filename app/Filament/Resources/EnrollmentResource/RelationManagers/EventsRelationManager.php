<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use App\Enums\PaymentStatus;
use App\Models\EnrollmentEvent;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/** Audit trail (who did what, when). Staff-only; never shown to the student. */
class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static bool $isLazy = false;

    protected static ?string $title = 'السجل';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $label = fn ($s) => $s ? (PaymentStatus::tryFrom($s)?->getLabel() ?? $s) : null;

        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('الوقت')->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('event')->label('الحدث')->formatStateUsing(fn ($state) => EnrollmentEvent::LABELS[$state] ?? $state),
                Tables\Columns\TextColumn::make('actor')->label('بواسطة')
                    ->formatStateUsing(fn ($state, EnrollmentEvent $r) => $r->user?->name ?? ['student' => 'الطالب', 'system' => 'النظام'][$state] ?? $state),
                Tables\Columns\TextColumn::make('to_status')->label('حالة السداد')->formatStateUsing(fn ($state, EnrollmentEvent $r) =>
                    $r->from_status ? $label($r->from_status) . ' ← ' . $label($state) : $label($state))->placeholder('—'),
                Tables\Columns\TextColumn::make('note')->label('ملاحظة')->wrap()->placeholder('—'),
            ]);
    }
}
