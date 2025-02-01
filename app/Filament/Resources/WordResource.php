<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WordResource\Pages;
use App\Filament\Resources\WordResource\RelationManagers;
use App\Models\Word;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get;
use Filament\Forms\Set;

class WordResource extends Resource
{
    protected static ?string $model = Word::class;

    protected static ?string $navigationGroup = 'Dictionary';

    protected static ?string $navigationIcon = null;

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Word Details')
                    ->schema([
                        Forms\Components\TextInput::make('word_gorontalo')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('word_indonesia')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name'),
                        RichEditor::make('description')
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
                            ->columnSpanFull(),
                    ]),

                Section::make('Audio')
                    ->schema([
                        // Opsi 1: Upload File Audio
                        FileUpload::make('audio_path')
                            ->label('Upload Audio File')
                            ->disk('public')
                            ->directory('word-audio')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->previewable(false)
                            ->openable()
                            ->live(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('word_gorontalo')->searchable(),
                Tables\Columns\TextColumn::make('word_indonesia')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->sortable()->default('-'),
                Tables\Columns\ViewColumn::make('audio')->view('filament.tables.columns.audio-player'),
            ])
            ->defaultSort('word_gorontalo', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWords::route('/'),
            'create' => Pages\CreateWord::route('/create'),
            'edit' => Pages\EditWord::route('/{record}/edit'),
        ];
    }
}
