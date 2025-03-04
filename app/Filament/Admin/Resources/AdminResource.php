<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdminResource\Pages;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Admin';

    protected static ?string $slug = 'admin';

    protected static ?string $breadcrumb = 'Admin';

    protected static ?string $navigationGroup = 'Others';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->placeholder('Please enter name')
                    ->autocomplete(false)
                    ->required(),
                TextInput::make('email')
                    ->placeholder('Please enter email')
                    ->autocomplete(false)
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('password')
                    ->placeholder('Please enter password')
                    ->autocomplete(false)
                    ->password()
                    ->minLength(8)
                    ->helperText('Password must be at least 8 characters')
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => !empty($state)),
                TextInput::make('role')
                    ->default('admin')
                    ->hidden(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('role', 'admin')
                    ->where('id', '!=', Auth::id())
                )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->getStateUsing(function ($record) {
                        return Carbon::parse($record->created_at)->timezone('Asia/Jakarta')->format('M d, Y H:i:s');
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Success')
                            ->body('Admin deleted successfully'),
                    )
                    ->failureNotification(
                        Notification::make()
                            ->danger()
                            ->title('Failed')
                            ->body('Failed to delete admin'),
                    ),
                ])
                ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Success')
                            ->body('Admin deleted successfully'),
                    )
                    ->failureNotification(
                        Notification::make()
                            ->danger()
                            ->title('Failed')
                            ->body('Failed to delete admin'),
                    ),
                ]),
            ])
            ->emptyStateHeading('Empty data')
            ->emptyStateDescription('No data found');
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
