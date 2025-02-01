<?php

namespace App\Filament\Resources\WordSubmissionResource\Pages;

use App\Filament\Resources\WordSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWordSubmission extends EditRecord
{
    protected static string $resource = WordSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
