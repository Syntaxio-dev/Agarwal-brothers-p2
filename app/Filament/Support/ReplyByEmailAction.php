<?php

namespace App\Filament\Support;

use App\Mail\CustomerMessage;
use App\Models\ContactMessage;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Support\EmailTemplates;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * "Reply by email" for an enquiry, contact message or job application.
 * Pick a ready-made template (editable under Settings, Email templates), adjust the text, send.
 * The customer's answer goes to the sender's own address; the e-mail is logged in the team notes.
 */
class ReplyByEmailAction
{
    public static function make(): Action
    {
        return Action::make('reply_email')
            ->label('Reply by email')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->visible(fn (?Model $record) => $record && filled($record->email))
            ->modalHeading(fn (Model $record) => 'Reply to ' . $record->name)
            ->modalDescription(fn (Model $record) => 'Sent to ' . $record->email . '. Their answer comes to your own email address (' . auth()->user()?->email . ').')
            ->modalSubmitActionLabel('Send email')
            ->modalWidth('3xl')
            ->fillForm(fn (Model $record) => [
                'template' => null,
                'subject' => 'Regarding your ' . (EmailTemplates::reference($record)),
                'body' => "Dear " . (explode(' ', trim((string) $record->name))[0] ?? '') . ",\n\n\n\nRegards,\n" . (auth()->user()?->name ?? 'Team Agarwal Brothers') . "\nAgarwal Brothers",
                'mark_contacted' => true,
            ])
            ->schema(fn (Model $record) => [
                Select::make('template')
                    ->label('Start from a template')
                    ->options(fn () => EmailTemplate::replies()->pluck('name', 'id')->all())
                    ->placeholder('Write my own')
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(function ($state, Set $set) use ($record) {
                        $template = $state ? EmailTemplate::find($state) : null;

                        if ($template) {
                            $set('subject', EmailTemplates::render($template->subject, $record));
                            $set('body', EmailTemplates::render($template->body, $record));
                        }
                    })
                    ->helperText('Picking a template fills the subject and text below. You can still change them before sending.'),
                TextInput::make('subject')->label('Subject')->required()->maxLength(200),
                Textarea::make('body')->label('Message')->required()->rows(12)->maxLength(5000),
                Toggle::make('mark_contacted')
                    ->label('Mark as Contacted after sending')
                    ->visible(fn () => ($record instanceof Enquiry || $record instanceof ContactMessage) && $record->status === 'new'),
            ])
            ->action(function (Model $record, array $data) {
                $sender = auth()->user();

                Mail::to($record->email)->queue(new CustomerMessage($data['subject'], $data['body'], $sender?->email, $sender?->name));

                if (method_exists($record, 'logActivity')) {
                    $record->logActivity("Emailed the customer. Subject: \"{$data['subject']}\"\n\n" . \Illuminate\Support\Str::limit($data['body'], 1200));
                }

                if (! empty($data['mark_contacted']) && ($record instanceof Enquiry || $record instanceof ContactMessage) && $record->status === 'new') {
                    $record->update(['status' => 'contacted']);
                }

                Notification::make()->title('Email sent to ' . $record->email)->success()->send();
            });
    }
}
