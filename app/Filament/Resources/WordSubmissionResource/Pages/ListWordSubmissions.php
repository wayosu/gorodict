<?php

namespace App\Filament\Resources\WordSubmissionResource\Pages;

use App\Filament\Resources\WordSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWordSubmissions extends ListRecords
{
    protected static string $resource = WordSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
