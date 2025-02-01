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
use App\Models\Word;
class WordSubmissionResource extends Resource
{
    protected static ?string $model = WordSubmission::class;

    protected static ?string $navigationGroup = 'Dictionary';

    protected static ?string $navigationIcon = null;

    protected static ?int $navigationSort = 3;

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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitted by')
                    ->visible($isAdmin),
                Tables\Columns\SelectColumn::make('status')
                    ->badge()
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled(fn (Model $record) => $record->status !== 'pending'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->action(function (WordSubmission $record) {
                        // Copy submission to words table
                        Word::create([
                            'word_gorontalo' => $record->word_gorontalo,
                            'word_indonesia' => $record->word_indonesia,
                            'category_id' => $record->category_id,
                            'description' => $record->description,
                            'audio_path' => $record->audio_path,
                        ]);

                        $record->update(['status' => 'approved']);
                    })
                    ->visible(fn (WordSubmission $record) => auth()->user()->isAdmin() && $record->status === 'pending')
                    ->color('success')
                    ->icon('heroicon-o-check'),

                Tables\Actions\Action::make('reject')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required(),
                    ])
                    ->action(function (WordSubmission $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    })
                    ->visible(fn (WordSubmission $record) => auth()->user()->isAdmin() && $record->status === 'pending')
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';

        return $data;
    }

    public static function getNavigationGroup(): ?string
    {
        return auth()->user()->isAdmin() ? 'Dictionary' : null;
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
