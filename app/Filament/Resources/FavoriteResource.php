<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FavoriteResource\Pages;
use App\Models\Favorite;
use App\Models\UserImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FavoriteResource extends Resource
{
    protected static ?string $model = Favorite::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Favorites';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                Forms\Components\Select::make('image_id')
                    ->relationship('image', 'title')
                    ->nullable(),

                Forms\Components\TextInput::make('api_image_id')
                    ->label('API Image ID')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_preview')
                    ->label('Image')
                    ->getStateUsing(function ($record) {
                        if ($record->image_id) {
                            $image = UserImage::find($record->image_id);
                            return $image && $image->file_path && Storage::disk('public')->exists($image->file_path)
                                ? Storage::url($image->file_path)
                                : null;
                        } elseif ($record->api_image_id) {
                            $apiImage = self::fetchApiImage($record->api_image_id);
                            return $apiImage['image_id'] ? self::buildImageUrl($apiImage['image_id']) : null;
                        }
                        return null;
                    })
                    ->defaultImageUrl(asset('images/placeholder.jpg'))
                    ->square(),

                Tables\Columns\TextColumn::make('api_image_id')
                    ->label('API Image ID')
                    ->default('N/A'),
            ])
            ->filters([
                Filter::make('image_id_search')
                    ->form([
                        Forms\Components\TextInput::make('image_id')
                            ->label('Search by Image ID')
                            ->placeholder('Enter Image ID or API Image ID'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['image_id'],
                                fn (Builder $query, $value): Builder => $query
                                    ->where('image_id', 'like', "%{$value}%")
                                    ->orWhere('api_image_id', 'like', "%{$value}%")
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFavorites::route('/'),
            'create' => Pages\CreateFavorite::route('/create'),
            'edit' => Pages\EditFavorite::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['image']);
    }

    private static function fetchApiImage($apiId)
    {
        $response = Http::get("https://api.artic.edu/api/v1/artworks/{$apiId}", [
            'fields' => 'id,title,image_id,description',
        ]);
        return $response->successful() ? $response->json('data') : [];
    }

    private static function buildImageUrl(?string $imageId, int $width = 600): ?string
    {
        return $imageId ? "https://www.artic.edu/iiif/2/{$imageId}/full/{$width},/0/default.jpg" : null;
    }
}