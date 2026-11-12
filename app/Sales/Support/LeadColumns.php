<?php

namespace App\Sales\Support;

use App\Sales\Models\SalesEnquiry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;

/**
 * The columns (and the "Open" window) shared by the manager's Leads screen and the rep desk's My leads, so the two never drift apart:
 * who (the manager only), client, what they asked for, the lead status, the next action, the last remark and the last contact.
 */
class LeadColumns
{
    public static function statusColor(?string $status): string
    {
        return match ($status ?? 'new') {
            'deal_done' => 'success',
            'deal_lost', 'not_interested' => 'gray',
            'call_not_picking', 'want_less_price', 'better_option' => 'warning',
            'price_sent', 'waiting' => 'info',
            default => 'primary',
        };
    }

    /** @return array<int, TextColumn> */
    public static function columns(bool $withRep): array
    {
        $tz = config('sales.timezone');

        return array_values(array_filter([
            $withRep ? TextColumn::make('user.name')->label('Rep')->searchable()->weight('medium') : null,
            TextColumn::make('client_name')->label('Client')->searchable()->description(fn (SalesEnquiry $r) => $r->contact_name),
            TextColumn::make('items_summary')->label('Asked for')->getStateUsing(fn (SalesEnquiry $r) => $r->itemsSummary())->wrap(),
            TextColumn::make('lead_status')->label('Lead status')->badge()
                ->formatStateUsing(fn (?string $state) => SalesEnquiry::LEAD_STATUSES[$state ?? 'new'] ?? $state)
                ->color(fn (?string $state) => self::statusColor($state))->sortable(),
            TextColumn::make('next_followup_at')->label('Next action')->sortable()
                ->state(fn (SalesEnquiry $r) => SalesInsights::leadReminder($r)['label'])
                ->color(fn (SalesEnquiry $r) => match (SalesInsights::leadReminder($r)['key']) {
                    'overdue' => 'danger', 'today', 'none' => 'warning', 'done' => 'gray', default => null,
                }),
            TextColumn::make('lead_remark')->label('Last remark')->placeholder('—')->limit(70)->wrap()->tooltip(fn (SalesEnquiry $r) => $r->lead_remark),
            TextColumn::make('last_followup_at')->label('Last contact')->since()->placeholder('Not yet')->sortable(),
            TextColumn::make('status')->label('Stage')->badge()->color('gray')->formatStateUsing(fn (string $state) => SalesEnquiry::STATUSES[$state] ?? $state)->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('opened_at')->label('Opened')->dateTime('d M Y', $tz)->sortable()->toggleable(isToggledHiddenByDefault: true),
        ]));
    }

    /** What the "Open" window of a lead shows: the follow-up first, then the enquiry's own details and timeline. */
    public static function infolist(bool $withRep): array
    {
        $tz = config('sales.timezone');

        return [
            Section::make('Follow-up')->columns(3)->schema(array_values(array_filter([
                $withRep ? TextEntry::make('user.name')->label('Rep') : null,
                TextEntry::make('lead_status')->label('Lead status')->badge()
                    ->formatStateUsing(fn (?string $state) => SalesEnquiry::LEAD_STATUSES[$state ?? 'new'] ?? $state)->color(fn (?string $state) => self::statusColor($state)),
                TextEntry::make('next_followup_at')->label('Next action')->state(fn (SalesEnquiry $r) => SalesInsights::leadReminder($r)['label'])
                    ->color(fn (SalesEnquiry $r) => SalesInsights::leadReminder($r)['key'] === 'overdue' ? 'danger' : null),
                TextEntry::make('last_followup_at')->label('Last contact')->dateTime('d M Y, h:i A', $tz)->placeholder('Not yet'),
                TextEntry::make('lead_remark')->label('Last remark')->placeholder('—')->columnSpanFull(),
            ]))),
            ...\App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource::infolistComponents(),
        ];
    }
}
