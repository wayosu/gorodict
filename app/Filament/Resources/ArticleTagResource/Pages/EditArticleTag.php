<?php

namespace App\Filament\Resources\ArticleTagResource\Pages;

use App\Filament\Resources\ArticleTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticleTag extends EditRecord
{
    protected static string $resource = ArticleTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
