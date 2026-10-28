<?php

namespace App\Sales\Support;

use App\Sales\Models\SalesExpense;
use App\Support\EnquiryExporter;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download the Sales lists as a CSV or Excel (.xlsx) file, exactly the rows currently listed or selected.
 * Reps type these values (notes, client names), so every text cell goes through the same formula guard as the enquiry export:
 * text that starts with = + - @ would run as a formula in a spreadsheet.
 */
class SalesExporter
{
    public static function expenses(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        return static::send('expenses', ['Date', 'Rep', 'Employee code', 'Category', 'Amount (INR)', 'Work area', 'Bill', 'Note', 'Has bill photo', 'Status', 'Reviewed by', 'Reviewed at (IST)', 'Reject reason', 'Id'],
            $query->clone()->reorder()->with(['user.salesProfile', 'reviewer']), $format,
            fn ($e) => array_merge(static::texts([
                $e->expense_date->format('Y-m-d'),
                $e->user->name,
                $e->user->salesProfile?->employee_code,
            ]), [SalesExpense::categoryLabel($e->category), $e->amount_paise / 100], static::texts([
                SalesExpense::WORK_AREAS[$e->work_area] ?? '',
                SalesExpense::BILL_TYPES[$e->bill_type] ?? '',
                $e->note,
                $e->hasBill() ? 'yes' : 'no',
                ucfirst($e->status),
                $e->reviewer?->name,
                $e->reviewed_at?->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                $e->reject_reason,
                $e->uuid,
            ])));
    }

    public static function meetings(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        return static::send('meetings', ['When (IST)', 'Rep', 'Client', 'Status', 'Result', 'Planned for (IST)', 'Route', 'Purpose', 'Discussion', 'Outcome', 'Follow-up (IST)', 'Id'],
            $query->clone()->reorder()->with(['user', 'route']), $format,
            fn ($m) => static::texts([
                $m->meeting_at->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                $m->user->name,
                $m->client_name,
                $m->statusLabel(),
                $m->resultLabel(),
                $m->scheduled_at?->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                $m->route && (int) $m->route->user_id === (int) $m->user_id ? $m->route->title : null,
                $m->purpose,
                $m->discussion,
                $m->outcome,
                $m->next_followup_at?->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                $m->uuid,
            ]));
    }

    public static function clients(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        return static::send('clients', ['Client', 'Group', 'Main number', 'Place', 'Works with', 'Added by', 'People', 'Notes', 'Id'],
            $query->clone()->reorder()->with(['owner', 'assignee', 'contacts']), $format,
            fn ($c) => static::texts([
                $c->name, $c->company, $c->phone, $c->city, $c->assignee?->name, $c->owner?->name,
                $c->contacts->map(fn ($p) => trim($p->name . ($p->position ? " ({$p->position})" : '') . ($p->phone ? " {$p->phone}" : '')))->implode('; '),
                $c->notes, $c->uuid,
            ]));
    }

    public static function enquiries(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        return static::send('enquiries', ['Opened (IST)', 'Rep', 'Client', 'Person', 'Status', 'Came through', 'Products', 'Preferred brands', 'Budget (INR)', 'Next follow-up (IST)', 'Last note', 'Id'],
            $query->clone()->reorder()->with('user'), $format,
            function ($e) {
                $items = collect($e->items ?? []);
                $last = collect($e->timeline ?? [])->last();

                return array_merge(static::texts([
                    $e->opened_at->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                    $e->user->name,
                    $e->client_name,
                    $e->contact_name,
                    $e->statusLabel(),
                    $e->sourceLabel(),
                    $items->map(fn ($i) => $i['product'] . ' x' . ($i['quantity'] ?? 1))->implode('; '),
                    $items->pluck('brands')->filter()->implode('; '),
                ]), [$e->budgetPaise() / 100], static::texts([
                    $e->next_followup_at?->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                    $last['text'] ?? '',
                    $e->uuid,
                ]));
            });
    }

    public static function reports(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        return static::send('reports', ['Date', 'Rep', 'Submitted (IST)', 'Calls', 'Talk time (min)', 'Meetings', 'New enquiries', 'Enquiries worked', 'Expenses (INR)', 'Notes', 'Tomorrow', 'Needs help with', 'Id'],
            $query->clone()->reorder()->with('user'), $format,
            fn ($r) => array_merge(static::texts([
                $r->report_date->format('Y-m-d'),
                $r->user->name,
                $r->submitted_at?->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
            ]), [
                (int) ($r->figures['calls'] ?? 0),
                round(((int) ($r->figures['talk_seconds'] ?? 0)) / 60),
                (int) ($r->figures['meetings'] ?? 0),
                (int) ($r->figures['enquiries_new'] ?? 0),
                (int) ($r->figures['enquiries_worked'] ?? 0),
                ((int) ($r->figures['expenses_paise'] ?? 0)) / 100,
            ], static::texts([$r->notes, $r->tomorrow_plan, $r->issues, $r->uuid])));
    }

    /** @param  callable(mixed): list<mixed>  $row */
    private static function send(string $name, array $headers, Builder $query, string $format, callable $row): StreamedResponse
    {
        $format = $format === 'csv' ? 'csv' : 'xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'absales') . '.' . $format;

        $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($tmp);
        $writer->addRow(Row::fromValues($headers));
        $query->lazyById(500)->each(fn ($record) => $writer->addRow(Row::fromValues($row($record))));
        $writer->close();

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, "sales-{$name}-" . now()->format('Y-m-d') . '.' . $format, [
            'Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** @return list<string> */
    private static function texts(array $values): array
    {
        return array_map(fn ($v) => EnquiryExporter::safe((string) ($v ?? '')), $values);
    }
}
