<?php

namespace App\Filament\Exports;

use App\Models\Article;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ArticleExporter extends Exporter
{
    protected static ?string $model = Article::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('title')
                ->label('Judul'),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('author_name')
                ->label('Penulis'),
            ExportColumn::make('category')
                ->label('Kategori'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('content')
                ->label('Konten'),
            ExportColumn::make('views')
                ->label('Dilihat'),
            ExportColumn::make('published_at')
                ->label('Diterbitkan Pada'),
            ExportColumn::make('created_at')
                ->label('Dibuat Pada'),
            ExportColumn::make('updated_at')
                ->label('Diperbarui Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor artikel telah selesai dan ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
