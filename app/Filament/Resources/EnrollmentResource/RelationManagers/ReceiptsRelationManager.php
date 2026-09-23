<?php

namespace App\Filament\Resources\EnrollmentResource\RelationManagers;

use App\Filament\Resources\EnrollmentResource;
use App\Models\PaymentReceipt;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/** Every receipt the student uploaded — kept even after a re-upload request. */
class ReceiptsRelationManager extends RelationManager
{
    protected static string $relationship = 'receipts';

    protected static bool $isLazy = false;

    protected static ?string $title = 'الإيصالات المرفوعة';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('original_name')->label('الملف'),
                Tables\Columns\TextColumn::make('mime_type')->label('النوع')->formatStateUsing(fn ($state) => strtoupper(explode('/', $state)[1] ?? $state)),
                Tables\Columns\TextColumn::make('size')->label('الحجم')
                    ->formatStateUsing(fn ($state) => $state >= 1048576 ? number_format($state / 1048576, 1) . ' MB' : max(1, round($state / 1024)) . ' KB'),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الرفع')->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('review_status')->label('المراجعة')->badge()
                    ->formatStateUsing(fn ($state) => PaymentReceipt::REVIEW[$state] ?? $state)
                    ->color(fn ($state) => ['accepted' => 'success', 'rejected' => 'danger'][$state] ?? 'warning'),
                Tables\Columns\TextColumn::make('reviewer.name')->label('راجعه')->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')->label('تحميل')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (PaymentReceipt $record) => EnrollmentResource::downloadReceipt($record)),
            ]);
    }
}
