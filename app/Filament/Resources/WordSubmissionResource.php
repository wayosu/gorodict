<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WordSubmissionResource\Pages;
use App\Filament\Resources\WordSubmissionResource\RelationManagers;
use App\Models\WordSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use App\Models\Word;


class WordSubmissionResource extends Resource
{
    protected static ?string $model = WordSubmission::class;

    public static function getNavigationIcon(): ?string
    {
        return auth()->user()->isAdmin() ? null : 'heroicon-o-plus';
    }

    public static function getNavigationSort(): ?int
    {
        return auth()->user()->isAdmin() ? 3 : 0;
    }

    public static function getNavigationGroup(): ?string
    {
        return auth()->user()->isAdmin() ? 'Dictionary' : null;
    }

    public static function getNavigationLabel(): string
    {
        return auth()->user()->isAdmin() ? 'Word Submissions' : 'Submit New Word';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Word Details')
                    ->schema([
                        Forms\Components\TextInput::make('word_gorontalo')
                            ->required(),
                        Forms\Components\TextInput::make('word_indonesia')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name'),
                        Forms\Components\RichEditor::make('description')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
                            ->required(),
                        Forms\Components\FileUpload::make('audio_path')
                            ->disk('public')
                            ->directory('word-submissions')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav'])
                            ->maxSize(5120),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = auth()->user()->isAdmin();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('word_gorontalo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('word_indonesia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\ViewColumn::make('audio')->view('filament.tables.columns.audio-player'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitted by')
                    ->visible($isAdmin),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->visible(fn (?WordSubmission $record): bool =>
                        $record?->status === 'rejected'
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->visible($isAdmin),
            ])
            ->actions([
                Tables\Actions\Action::make('view_reason')
                    ->label('View Reason')
                    ->icon('heroicon-o-information-circle')
                    ->modalWidth('md')
                    ->modalHeading('Rejection Reason')
                    ->modalContent(fn (WordSubmission $record): HtmlString =>
                        new HtmlString(nl2br(e($record->rejection_reason)))
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn (?WordSubmission $record): bool =>
                        $record?->status === 'rejected'
                    )
                    ->color('danger'),

                Tables\Actions\Action::make('approve')
                    ->action(function (WordSubmission $record) {
                        Word::create([
                            'word_gorontalo' => $record->word_gorontalo,
                            'word_indonesia' => $record->word_indonesia,
                            'category_id' => $record->category_id,
                            'description' => $record->description,
                            'audio_path' => $record->audio_path,
                        ]);

                        $record->update(['status' => 'approved']);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (WordSubmission $record) =>
                        $isAdmin && $record->status === 'pending'
                    )
                    ->color('success')
                    ->icon('heroicon-o-check'),

                Tables\Actions\Action::make('reject')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (WordSubmission $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    })
                    ->visible(fn (WordSubmission $record) =>
                        $isAdmin && $record->status === 'pending'
                    )
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-x-mark'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // User biasa hanya bisa melihat submissionnya sendiri
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    // Kontrol akses berdasarkan role
    public static function canCreate(): bool
    {
        return true; // Semua user bisa membuat submission
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Tidak ada yang bisa edit submission
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->isAdmin(); // Hanya admin yang bisa hapus
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWordSubmissions::route('/'),
            'create' => Pages\CreateWordSubmission::route('/create'),
            'edit' => Pages\EditWordSubmission::route('/{record}/edit'),
        ];
    }
}
