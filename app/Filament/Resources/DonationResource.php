<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static \UnitEnum|string|null $navigationGroup = 'Finance';

    public static function canCreate(): bool
    {
        return false; // Donations are created via Stripe; admins only view/manage
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Donation Info')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('artist_profile_id')
                        ->relationship('artist', 'stage_name')
                        ->searchable()
                        ->disabled()
                        ->label('Artist'),
                    Forms\Components\Select::make('track_id')
                        ->relationship('track', 'title')
                        ->searchable()
                        ->nullable()
                        ->disabled(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('$')
                        ->disabled(),
                    Forms\Components\Select::make('type')
                        ->options(['unlock' => 'Unlock', 'tip' => 'Tip'])
                        ->disabled(),
                    Forms\Components\Select::make('status')
                        ->options(['pending' => 'Pending', 'completed' => 'Completed', 'refunded' => 'Refunded', 'failed' => 'Failed'])
                        ->required(),
                    Forms\Components\TextInput::make('stripe_payment_intent_id')
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('message')
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->label('Donor'),
                Tables\Columns\TextColumn::make('artist.stage_name')->searchable()->label('Artist'),
                Tables\Columns\TextColumn::make('track.title')->label('Track')->limit(30)->toggleable(),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors(['primary' => 'unlock', 'success' => 'tip']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'completed', 'warning' => 'pending', 'danger' => fn ($state) => in_array($state, ['failed', 'refunded'])]),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['unlock' => 'Unlock', 'tip' => 'Tip']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed', 'refunded' => 'Refunded', 'failed' => 'Failed']),
            ])
            ->actions([Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDonations::route('/'),
            'edit'   => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
