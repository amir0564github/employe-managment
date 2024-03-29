<?php

namespace App\Filament\Resources\StateResource\RelationManagers;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use App\Models\Country;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Select::make('country_id')
                ->label('Country')
                ->options(Country::all()->pluck('name', 'id')->toArray())
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('state_id', null)),
            Select::make('state_id')
                ->label('State')
                ->required()
                ->options(function (callable $get) {
                    $country = Country::find($get('country_id'));
                    if (!$country) {
                        return State::all()->pluck('name', 'id');
                    }
                    return $country->states->pluck('name', 'id');
                })
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),
            Select::make('city_id')
                ->label('City')
                ->options(function (callable $get) {
                    $state = State::find($get('state_id'));
                    if (!$state) {
                        return City::all()->pluck('name', 'id');
                    }
                    return $state->cities->pluck('name', 'id');
                })
                ->required()
                ->reactive(),
            Select::make('department_id')
                ->relationship('department', 'name')->required(),
            TextInput::make('first_name')->required()->maxLength(255),
            TextInput::make('last_name')->required()->maxLength(255),
            TextInput::make('address')->required()->maxLength(255),
            TextInput::make('zip_code')->required()->maxLength(7),
            DatePicker::make('birth_date')->required(),
            DatePicker::make('date_hired')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('first_name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
