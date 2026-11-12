<?php

namespace App\Sales\Rep\Actions;

use App\Sales\Models\SalesEnquiry;
use App\Sales\Support\RepEnquiryService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

/**
 * "Follow-up" on a lead: the status, what the client said (remark) and the next reminder, exactly the phone's follow-up sheet. Used by My leads
 * and by My enquiries, so the rep has one way to do it. Everything goes through RepEnquiryService::leadFollowup (the phone's rules).
 */
class LeadFollowupAction
{
    public static function make(): Action
    {
        return Action::make('leadFollowup')->label('Follow-up')->icon(Heroicon::OutlinedPhone)
            ->visible(fn (SalesEnquiry $r) => $r->isOpen())
            ->modalHeading(fn (SalesEnquiry $r) => 'Follow-up: ' . $r->client_name)
            ->modalDescription(fn (SalesEnquiry $r) => $r->lead_remark ? 'Last time: ' . $r->lead_remark : 'Asked for ' . $r->itemsSummary(2))
            ->modalSubmitActionLabel('Save follow-up')
            ->fillForm(fn (SalesEnquiry $r) => [
                'lead_status' => $r->lead_status ?? 'new',
                'lead_remark' => null,
                'next_followup_at' => $r->next_followup_at ?? SalesEnquiry::suggestedReminder($r->lead_status ?? 'new'),
            ])
            ->schema([
                Select::make('lead_status')->label('Follow-up status')->options(SalesEnquiry::LEAD_STATUSES)->required()->native(false)->live()
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        // the status suggests a reminder (Deal done / Deal lost have none: the enquiry is finished)
                        $set('next_followup_at', $state ? SalesEnquiry::suggestedReminder($state)?->utc() : null);
                    }),
                Textarea::make('lead_remark')->label('Remark (what the client said)')->rows(3)->maxLength(SalesEnquiry::LEAD_REMARK_MAX),
                DateTimePicker::make('next_followup_at')->label('Next reminder')->seconds(false)->native(false)->timezone(config('sales.timezone'))
                    ->visible(fn (Get $get) => ! array_key_exists((string) $get('lead_status'), SalesEnquiry::LEAD_CLOSES))
                    ->helperText('So this lead is not forgotten. Deal done / Deal lost finish the enquiry.'),
            ])
            ->action(function (SalesEnquiry $record, array $data, Action $action) {
                try {
                    app(RepEnquiryService::class)->leadFollowup($record, auth()->user(), $data['lead_status'], $data['lead_remark'] ?? null, $data['next_followup_at'] ?? null);
                } catch (ValidationException $e) {
                    Notification::make()->title(collect($e->errors())->flatten()->first() ?: 'Could not save the follow-up.')->danger()->send();
                    $action->halt();

                    return;
                }

                Notification::make()->title(isset(SalesEnquiry::LEAD_CLOSES[$data['lead_status']]) ? 'Enquiry finished: ' . SalesEnquiry::LEAD_STATUSES[$data['lead_status']] : 'Follow-up saved')->success()->send();
            });
    }
}
