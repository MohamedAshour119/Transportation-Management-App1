<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationGroup = 'Management';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vehicle Information')
                    ->schema([
                        Forms\Components\TextInput::make('make')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('model')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 1),
                        
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(255)
                            ->suffix('passengers'),
                        
                        Forms\Components\TextInput::make('plate_number')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('vehicle_identification_number')
                            ->label('VIN')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('make')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('model')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('company.name')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('capacity')
                    ->suffix(' passengers')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('plate_number')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('vehicle_identification_number')
                    ->label('VIN')
                    ->toggleable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('trips_count')
                    ->counts('trips')
                    ->label('Total Trips')
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('make')
                    ->options(fn () => Vehicle::distinct()->pluck('make', 'make')->toArray()),
                
                Tables\Filters\Filter::make('high_capacity')
                    ->query(fn (Builder $query): Builder => $query->where('capacity', '>=', 20))
                    ->label('High Capacity (20+)'),
                
                Tables\Filters\Filter::make('has_trips')
                    ->query(fn (Builder $query): Builder => $query->has('trips'))
                    ->label('Has Trips'),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
