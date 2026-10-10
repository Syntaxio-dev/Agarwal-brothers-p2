<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Product'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('company')
                    ->default(null),
                TextInput::make('budget')
                    ->default(null),
                TextInput::make('order_location')
                    ->default(null),
                Repeater::make('items')
                    ->label('Requested products (enquiry list)')
                    ->relationship()
                    ->schema([
                        TextInput::make('product_name')->label('Product')->disabled()->dehydrated(false)->columnSpan(2),
                        TextInput::make('brand_name')->label('Brand')->disabled()->dehydrated(false),
                        TextInput::make('quantity')->label('Qty')->disabled()->dehydrated(false),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->visible(fn ($record) => (bool) $record?->items()->exists())
                    ->columnSpanFull(),
                Textarea::make('message')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(\App\Models\Enquiry::STATUSES)
                    ->required()
                    ->default('new'),
                Select::make('assigned_to')
                    ->label('Assigned to')
                    ->options(fn () => \App\Models\User::assignable('enquiries'))
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->helperText('The team member who looks after this enquiry. They get an email when you assign it.'),
            ]);
    }
}
