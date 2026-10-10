<?php

namespace App\Support;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Download enquiries as a CSV or Excel (.xlsx) file, exactly the rows currently listed/selected. */
class EnquiryExporter
{
    public const HEADERS = [
        'Reference', 'Received (IST)', 'Name', 'Email', 'Phone', 'Company', 'Order location', 'Budget',
        'Type', 'Products', 'Message', 'Status', 'Assigned to', 'Team notes',
    ];

    public static function download(Builder $query, string $format = 'xlsx'): StreamedResponse
    {
        $format = $format === 'csv' ? 'csv' : 'xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'abenq') . '.' . $format;

        $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($tmp);
        $writer->addRow(Row::fromValues(static::HEADERS));

        $query->clone()->reorder()->with(['items', 'product', 'assignee'])->withCount('teamNotes')
            ->lazyById(500)
            ->each(fn (Enquiry $e) => $writer->addRow(Row::fromValues(static::row($e))));

        $writer->close();

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, 'enquiries-' . now()->format('Y-m-d') . '.' . $format, [
            'Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** @return list<string|int> */
    public static function row(Enquiry $e): array
    {
        $isGroup = $e->items->isNotEmpty();

        $products = $isGroup
            ? $e->items->map(fn ($i) => $i->product_name . ($i->quantity > 1 ? ' x' . $i->quantity : ''))->implode('; ')
            : (string) ($e->product?->name ?? '');

        return array_map([static::class, 'safe'], [
            $e->reference(),
            $e->created_at?->timezone('Asia/Kolkata')->format('Y-m-d H:i') ?? '',
            (string) $e->name,
            (string) $e->email,
            (string) $e->phone,
            (string) $e->company,
            (string) $e->order_location,
            (string) $e->budget,
            $isGroup ? 'Group (' . $e->items->count() . ' products)' : ($e->product ? 'Single product' : 'General'),
            $products,
            (string) $e->message,
            Enquiry::STATUSES[$e->status] ?? ucfirst((string) $e->status),
            (string) ($e->assignee?->name ?? ''),
            (string) ($e->team_notes_count ?? 0),
        ]);
    }

    /**
     * Customers type these values, and spreadsheet programs run text that starts with = + - @ as a formula.
     * A leading apostrophe makes it plain text. Phone numbers (+91 98...) are left alone.
     */
    public static function safe(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $risky = in_array($value[0], ['=', '@', "\t", "\r"], true)
            || (in_array($value[0], ['+', '-'], true) && ! preg_match('/^[+-]?[\d\s().-]+$/', $value));

        return $risky ? "'" . $value : $value;
    }
}
