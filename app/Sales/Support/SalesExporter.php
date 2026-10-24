<?php

namespace App\Sales\Support;

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
        return static::send('expenses', ['Date', 'Rep', 'Employee code', 'Category', 'Amount (INR)', 'Note', 'Has bill', 'Status', 'Reviewed by', 'Reviewed at (IST)', 'Reject reason', 'Id'],
            $query->clone()->reorder()->with(['user.salesProfile', 'reviewer']), $format,
            fn ($e) => array_merge(static::texts([
                $e->expense_date->format('Y-m-d'),
                $e->user->name,
                $e->user->salesProfile?->employee_code,
            ]), [ucfirst($e->category), $e->amount_paise / 100], static::texts([
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
        return static::send('meetings', ['When (IST)', 'Rep', 'Client', 'Purpose', 'Discussion', 'Outcome', 'Follow-up (IST)', 'Id'],
            $query->clone()->reorder()->with('user'), $format,
            fn ($m) => static::texts([
                $m->meeting_at->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                $m->user->name,
                $m->client_name,
                $m->purpose,
                $m->discussion,
                $m->outcome,
                $m->next_followup_at?->timezone(config('sales.timezone'))->format('Y-m-d H:i'),
                $m->uuid,
            ]));
    }

    public static function clients(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        return static::send('clients', ['Name', 'Company', 'Phone', 'City', 'Rep', 'Notes', 'Id'],
            $query->clone()->reorder()->with('owner'), $format,
            fn ($c) => static::texts([$c->name, $c->company, $c->phone, $c->city, $c->owner->name, $c->notes, $c->uuid]));
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
