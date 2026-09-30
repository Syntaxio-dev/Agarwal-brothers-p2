<?php

namespace App\Filament\Resources\Enquiries\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M, h:i A')
                    ->sortable(),
                TextColumn::make('name'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable(),
                TextColumn::make('budget')
                    ->label('Budget'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'contacted' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}