<?php

namespace App\Support;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * THE IMPORT RULE (owner, 25 Nov 2026): wherever a screen lets someone import a CSV or an Excel file, it also offers a TEMPLATE to download,
 * in CSV and in Excel (.xlsx), with the exact column names and a few example rows, so a file never fails because of its format. The Excel
 * template has a second sheet, "How to fill", that says what each column is, whether it is needed and what to write. The importer accepts
 * both kinds of file (asCsv() turns an .xlsx into a CSV first). A new import screen without a template is a bug: tests/Feature/ImportTemplateRuleTest
 * fails when a screen takes a CSV upload and does not use this class.
 */
class ImportTemplate
{
    /**
     * @param  string  $name  file name without the extension, for example "clients-import-template"
     * @param  list<list<string>>  $rows  the header row first, then the example rows
     * @param  list<list<string>>  $guide  [column, needed?, what to write, example]: becomes the "How to fill" sheet of the Excel file
     */
    public static function download(string $name, array $rows, string $format = 'xlsx', array $guide = []): StreamedResponse
    {
        $format = $format === 'csv' ? 'csv' : 'xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'abtemplate') . '.' . $format;

        $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;   // the CSV writer adds the BOM that lets Excel read it as UTF-8
        $writer->openToFile($tmp);

        if ($format === 'xlsx') {
            $writer->getCurrentSheet()->setName('Template');
        }
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues(array_map('strval', $row)));
        }

        if ($format === 'xlsx' && $guide) {
            $writer->addNewSheetAndMakeItCurrent()->setName('How to fill');
            $writer->addRow(Row::fromValues(['Column', 'Needed?', 'What to write', 'Example']));
            foreach ($guide as $line) {
                $writer->addRow(Row::fromValues(array_map('strval', $line)));
            }
        }
        $writer->close();

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $name . '.' . $format, [
            'Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * The file as a CSV path the CSV importers can read: an .xlsx is read (first sheet) and written to a temporary CSV, anything else is
     * returned as it is. The caller removes the temporary file when `$path !== $returned`.
     */
    public static function asCsv(string $path, string $extension): string
    {
        if (strtolower($extension) !== 'xlsx') {
            return $path;
        }

        $csv = tempnam(sys_get_temp_dir(), 'abimport') . '.csv';
        $out = fopen($csv, 'wb');
        $reader = new XlsxReader;
        $reader->open($path);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    fputcsv($out, array_map(fn ($cell) => self::cell($cell), $row->toArray()));
                }
                break;   // the first sheet only: the template's second sheet is the guide
            }
        } finally {
            $reader->close();
            fclose($out);
        }

        return $csv;
    }

    /** A phone number typed as a number in Excel arrives as 9876543210.0: back to plain digits. */
    private static function cell(mixed $cell): string
    {
        if ($cell instanceof \DateTimeInterface) {
            return $cell->format('Y-m-d');
        }
        if (is_float($cell) && floor($cell) === $cell && abs($cell) < 1e15) {
            return (string) (int) $cell;
        }

        return trim((string) $cell);
    }
}
