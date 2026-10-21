<?php

namespace App\Filament\Resources\EnquiryQuestions;

use App\Filament\Concerns\RestrictedByRole;
use App\Filament\Resources\EnquiryQuestions\Pages\CreateEnquiryQuestion;
use App\Filament\Resources\EnquiryQuestions\Pages\EditEnquiryQuestion;
use App\Filament\Resources\EnquiryQuestions\Pages\ListEnquiryQuestions;
use App\Models\EnquiryQuestion;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnquiryQuestionResource extends Resource
{
    use RestrictedByRole;

    // same access as Enquiries: whoever works the enquiries decides what the form asks
    protected static string $accessKey = 'enquiries';

    protected static ?string $model = EnquiryQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Enquiry questions';

    protected static ?string $modelLabel = 'enquiry question';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('vertical_id')
                ->label('Ask for which vertical?')
                ->relationship('vertical', 'name')
                ->searchable()
                ->preload()
                ->placeholder('All products (every vertical)')
                ->helperText('Leave empty to ask everyone. Otherwise only visitors enquiring about a product in this vertical see these questions.')
                ->columnSpanFull(),

            // Creating: add many questions for the vertical in one go.
            Repeater::make('questions')
                ->label('Questions')
                ->schema(static::questionFields())
                ->defaultItems(1)
                ->minItems(1)
                ->maxItems(8)
                ->addActionLabel('Add another question')
                ->itemLabel(fn (array $state): ?string => filled($state['question'] ?? null) ? $state['question'] : null)
                ->collapsible()
                ->columns(2)
                ->visible(fn (string $operation) => $operation === 'create')
                ->columnSpanFull(),

            // Editing: one question.
            ...array_map(
                fn ($field) => $field->visible(fn (string $operation) => $operation === 'edit'),
                static::questionFields(),
            ),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower number comes first. You can also drag rows in the list.')
                ->visible(fn (string $operation) => $operation === 'edit'),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->visible(fn (string $operation) => $operation === 'edit'),
        ]);
    }

    /** The fields of ONE question (used in the create repeater and on the edit page). */
    protected static function questionFields(): array
    {
        return [
            TextInput::make('question')
                ->required()
                ->maxLength(150)
                ->helperText('Short and simple, for example "What will you weigh?". Visitors must answer it before they can send the enquiry.')
                ->columnSpanFull(),
            Select::make('type')
                ->label('Answer type')
                ->options(EnquiryQuestion::TYPES)
                ->default('choice')
                ->required()
                ->live()
                ->helperText('"Pick one answer" shows tap-able choices, the quickest for the visitor.'),
            TagsInput::make('options')
                ->label('Choices')
                ->placeholder('Type a choice and press Enter')
                ->visible(fn ($get) => $get('type') === 'choice')
                ->required(fn ($get) => $get('type') === 'choice')
                ->rules(['array', 'min:2', 'max:8'])
                ->helperText('2 to 8 short choices, for example "Under 50,000", "50,000 to 2 lakh", "Above 2 lakh", "Not sure yet".'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')->searchable()->weight('medium')->wrap(),
                TextColumn::make('vertical.name')->label('Vertical')->placeholder('All products')->badge(),
                TextColumn::make('type')->formatStateUsing(fn ($state) => EnquiryQuestion::TYPES[$state] ?? $state),
                TextColumn::make('options')->label('Choices')->formatStateUsing(fn ($state) => implode(', ', (array) $state))->limit(60)->placeholder('-'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('vertical_id')->label('Vertical')->relationship('vertical', 'name'),
            ])
            ->groups([\Filament\Tables\Grouping\Group::make('vertical.name')->label('Vertical')->collapsible()])
            ->defaultGroup('vertical.name')
            ->groupingSettingsHidden()
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No questions yet')
            ->emptyStateDescription('Add a few quick, easy questions (budget, application...). Visitors must answer them before sending an enquiry.')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /** A typed question has no choices: drop any left over from switching the type. */
    public static function cleanOptions(array $data): array
    {
        if (($data['type'] ?? 'choice') !== 'choice') {
            $data['options'] = null;
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiryQuestions::route('/'),
            'create' => CreateEnquiryQuestion::route('/create'),
            'edit' => EditEnquiryQuestion::route('/{record}/edit'),
        ];
    }
}
