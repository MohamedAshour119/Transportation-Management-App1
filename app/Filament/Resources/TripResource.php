<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Models\Trip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Trip Details')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                // Reset dependent selects when company changes
                                $set('driver_id', null);
                                $set('vehicle_id', null);
                            }),

                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'full_name', modifyQueryUsing: function (Builder $query, Get $get) {
                                if ($get('company_id')) {
                                    $query->where('company_id', $get('company_id'));
                                }
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->relationship('vehicle', 'plate_number', modifyQueryUsing: function (Builder $query, Get $get) {
                                if ($get('company_id')) {
                                    $query->where('company_id', $get('company_id'));
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->make} {$record->model} ({$record->plate_number})")
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'scheduled' => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule & Location')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $set('end_time', null);
                                }
                            }),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->required()
                            ->native(false)
                            ->after('start_time'),

                        Forms\Components\TextInput::make('start_location')
                            ->label('Start Location')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('end_location')
                            ->label('End Location')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Trip Metrics')
                    ->schema([
                        Forms\Components\TextInput::make('distance')
                            ->numeric()
                            ->step('0.01')
                            ->suffix(' km'),

                        Forms\Components\TextInput::make('fuel_consumption')
                            ->numeric()
                            ->step('0.01')
                            ->suffix(' L'),

                        Forms\Components\TextInput::make('fuel_price')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $consumption = (float) ($get('fuel_consumption') ?? 0);
                                $price = (float) ($state ?? 0);
                                $set('fuel_cost', $consumption && $price ? round($price * $consumption, 2) : null);
                            }),

                        Forms\Components\TextInput::make('fuel_cost')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$')
                            ->readOnly(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Costs')
                    ->schema([
                        Forms\Components\TextInput::make('insurance_cost')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('total_cost',
                                    (float) ($get('fuel_cost') ?? 0)
                                    + (float) ($get('insurance_cost') ?? 0)
                                    + (float) ($get('maintenance_cost') ?? 0)
                                );
                            }),

                        Forms\Components\TextInput::make('maintenance_cost')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('total_cost',
                                    (float) ($get('fuel_cost') ?? 0)
                                    + (float) ($get('insurance_cost') ?? 0)
                                    + (float) ($get('maintenance_cost') ?? 0)
                                );
                            }),

                        Forms\Components\TextInput::make('total_cost')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$')
                            ->readOnly(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('driver.full_name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Vehicle')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->vehicle ? "{$record->vehicle->make} {$record->vehicle->model} ({$record->vehicle->plate_number})" : '-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'primary',
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', (string) $state)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distance')
                    ->label('Distance')
                    ->sortable()
                    ->suffix(' km')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fuel_cost')
                    ->label('Fuel Cost')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('insurance_cost')
                    ->label('Insurance')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('maintenance_cost')
                    ->label('Maintenance')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('active_now')
                    ->label('Active Now')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', 'in_progress')
                        ->where('start_time', '<=', now())
                        ->where('end_time', '>=', now())
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_time', 'desc');
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
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
