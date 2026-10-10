<?php

namespace App\Filament\Resources\EmailTemplates;

use App\Filament\Concerns\RestrictedByRole;
use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Models\EmailTemplate;
use App\Support\EmailTemplates;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/** The wording of automatic replies and ready-made manual replies. Administrators only. */
class EmailTemplateResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'email-templates';

    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Email templates';

    protected static ?string $modelLabel = 'email template';

    protected static ?string $recordTitleAttribute = 'name';

    // The set of templates is fixed; only their wording can change.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Email')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')->label('Template')->disabled()->dehydrated(false),
                    Toggle::make('is_enabled')
                        ->label(fn (?EmailTemplate $record) => $record?->isAuto() ? 'Send this email automatically' : 'Offer this template when replying')
                        ->helperText(fn (?EmailTemplate $record) => $record?->isAuto()
                            ? 'Switch off if you do not want customers to receive this automatic message. Your team can still reply by hand.'
                            : 'Switch off to hide it from the template list in the Reply by email window.'),
                    TextInput::make('subject')->required()->maxLength(200),
                    Textarea::make('body')
                        ->label('Message')
                        ->required()
                        ->rows(14)
                        ->maxLength(5000)
                        ->helperText('Plain text. Blank lines become paragraphs. Use the placeholders listed below.'),
                ]),
            Section::make('Placeholders you can use')
                ->description("Type them exactly as shown. They are replaced with the customer's details when the email is sent.")
                ->columnSpanFull()
                ->collapsible()
                ->schema([
                    Placeholder::make('placeholders')
                        ->hiddenLabel()
                        ->content(function () {
                            $rows = collect(EmailTemplates::placeholders())
                                ->map(fn ($what, $tag) => '<tr><td style="padding:4px 16px 4px 0;font-family:monospace;white-space:nowrap">' . e($tag) . '</td><td style="padding:4px 0">' . e($what) . '</td></tr>')
                                ->implode('');

                            return new HtmlString('<table style="font-size:14px">' . $rows . '</table>');
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Template')->weight('medium')->description(fn (EmailTemplate $r) => $r->subject),
                TextColumn::make('kind')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'auto' ? 'Automatic' : 'Manual reply')
                    ->color(fn (string $state) => $state === 'auto' ? 'primary' : 'info'),
                ToggleColumn::make('is_enabled')->label('On'),
                TextColumn::make('updated_at')->label('Last edited')->since()->color('gray'),
            ])
            ->defaultSort('id')
            ->recordActions([EditAction::make()->label('Edit')])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplates::route('/'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
