<?php

namespace App\Sales\Support;

use App\Models\User;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesPosition;
use Illuminate\Support\Facades\DB;

/**
 * Fills the client pool from a spreadsheet saved as CSV: the manager already has the numbers, the reps get them on their phones.
 * One row = one person at a client. Columns (any order, any capitals): client, place, contact, position, phone, email, notes.
 * A client that already exists (same name and place) is reused; a number that is already in the pool is skipped, never doubled.
 */
class ClientImporter
{
    public const MAX_ROWS = 3000;

    private const ALIASES = [
        'client' => ['client', 'client name', 'company', 'company name', 'name'],
        'place' => ['place', 'city', 'location', 'town'],
        'contact' => ['contact', 'contact name', 'person', 'person name', 'contact person'],
        'position' => ['position', 'designation', 'role', 'title'],
        'phone' => ['phone', 'mobile', 'number', 'phone number', 'mobile number', 'contact number'],
        'email' => ['email', 'e-mail', 'mail'],
        'notes' => ['notes', 'note', 'remarks'],
    ];

    /** A row of the template that still says this in its Client cell is an example: it is never imported. */
    public const EXAMPLE_MARK = '(delete this row)';

    /** Header + example rows of the downloadable template (CSV and Excel). @return list<list<string>> */
    public static function sample(): array
    {
        return [
            ['Client', 'Place', 'Contact', 'Position', 'Phone', 'Email', 'Notes'],
            ['Example Pvt Ltd ' . self::EXAMPLE_MARK, 'Jaipur', 'Rohit Sharma', 'Purchase Manager', '9876543210', 'rohit@example.com', 'Met at the trade fair'],
            ['Example Pvt Ltd ' . self::EXAMPLE_MARK, 'Jaipur', 'Anita Verma', 'Lab Head', '9123456780', '', 'A second person at the same client: write the client again'],
        ];
    }

    /** The "How to fill" sheet of the Excel template: [column, needed?, what to write, example]. @return list<list<string>> */
    public static function guide(): array
    {
        return [
            ['Client', 'Yes', 'The company or customer. Write the same name on every row of the same client.', 'JK Cement'],
            ['Place', 'Advised', 'Town or city (letters only). The team filters clients by place.', 'Jaipur'],
            ['Contact', 'No', 'The person you can call. One row is one person.', 'Rohit Sharma'],
            ['Position', 'No', 'Job title, for example from the Positions list.', 'Purchase Manager'],
            ['Phone', 'No', 'A mobile (10 digits) or a number with its country code. A number that is already saved is skipped.', '9876543210'],
            ['Email', 'No', 'One e-mail address.', 'rohit@example.com'],
            ['Notes', 'No', 'Anything the rep should know.', 'Met at the trade fair'],
            ['', '', 'Delete the two example rows (they say "delete this row") or leave them: they are never imported. Up to ' . self::MAX_ROWS . ' rows. Save as .xlsx, or as CSV UTF-8.', ''],
        ];
    }

    /** @return array{rows: int, clients_created: int, clients_reused: int, contacts_created: int, duplicates: list<string>, skipped: list<string>} */
    public static function fromCsv(string $path, ?int $assignTo, User $by): array
    {
        $summary = ['rows' => 0, 'clients_created' => 0, 'clients_reused' => 0, 'contacts_created' => 0, 'duplicates' => [], 'skipped' => []];

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $summary['skipped'][] = 'The file could not be read.';

            return $summary;
        }

        try {
            $first = (string) fgets($handle);
            $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($first, $d))->first();
            rewind($handle);

            $header = fgetcsv($handle, 0, $delimiter);
            $map = $header ? self::columns($header) : [];

            if (! isset($map['client'])) {
                $summary['skipped'][] = 'The first row must name the columns, and one of them must be "Client".';

                return $summary;
            }

            DB::transaction(function () use ($handle, $delimiter, $map, $assignTo, $by, &$summary) {
                $line = 1;

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $line++;

                    if ($summary['rows'] >= self::MAX_ROWS) {
                        $summary['skipped'][] = 'Stopped after ' . self::MAX_ROWS . ' rows. Import the rest in a second file.';
                        break;
                    }
                    if ($row === [null] || ! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                        continue;
                    }
                    if (isset($map['client']) && str_contains(strtolower((string) ($row[$map['client']] ?? '')), self::EXAMPLE_MARK)) {
                        continue;   // an example row of the template
                    }

                    $summary['rows']++;
                    self::row($map, $row, $line, $assignTo, $by, $summary);
                }
            });
        } finally {
            fclose($handle);
        }

        $summary['duplicates'] = array_slice($summary['duplicates'], 0, 30);
        $summary['skipped'] = array_slice($summary['skipped'], 0, 30);

        return $summary;
    }

    /** @return array<string, int> field => column index */
    private static function columns(array $header): array
    {
        $map = [];

        foreach ($header as $index => $title) {
            $title = mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $title) ?? ''));

            foreach (self::ALIASES as $field => $names) {
                if (! isset($map[$field]) && in_array($title, $names, true)) {
                    $map[$field] = $index;
                }
            }
        }

        return $map;
    }

    private static function row(array $map, array $row, int $line, ?int $assignTo, User $by, array &$summary): void
    {
        $get = fn (string $field, int $max) => isset($map[$field]) ? mb_substr(trim((string) ($row[$map[$field]] ?? '')), 0, $max) : '';

        $name = $get('client', 150);
        $place = $get('place', 100);
        $person = $get('contact', 150);
        $position = $get('position', 100);
        $phone = $get('phone', 30);
        $email = $get('email', 150);
        $notes = $get('notes', 1000);

        if (mb_strlen($name) < 2) {
            $summary['skipped'][] = "Row $line: the client name is missing.";

            return;
        }

        $normal = $phone === '' ? null : PhoneNumber::normalize($phone);
        if ($phone !== '' && $normal === null) {
            $summary['skipped'][] = "Row $line ($name): the phone number \"$phone\" is not valid.";

            return;
        }
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        // the same client = the same name in the same place
        $client = SalesClient::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->where(fn ($q) => $place === '' ? $q->whereNull('city')->orWhere('city', '') : $q->whereRaw('LOWER(city) = ?', [mb_strtolower($place)]))
            ->first();

        if ($client) {
            $summary['clients_reused']++;
        } else {
            $client = SalesClient::create([
                'name' => $name,
                'city' => $place ?: null,
                'notes' => null,
                'user_id' => $by->id,
            ]);
            if ($assignTo) {
                (new \App\Sales\Actions\SetClientAccess())->give($client, (int) $assignTo, $by);   // the one write path for "who sees it"
            }
            $summary['clients_created']++;
        }

        if ($person === '' && $normal === null) {
            return; // a row that only names the client
        }

        if ($normal !== null) {
            $owner = self::numberOwner($normal);

            if ($owner !== null) {
                $summary['duplicates'][] = "Row $line: $phone is already saved under $owner.";

                return;
            }
        }

        SalesContact::create([
            'client_uuid' => $client->uuid,
            'user_id' => $by->id,
            'name' => $person !== '' ? $person : $name,
            'position' => $position ?: null,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'notes' => $notes ?: null,
        ]);
        $summary['contacts_created']++;

        if ($position !== '' && ! SalesPosition::where('name', $position)->exists() && SalesPosition::count() < 300 && preg_match('/^[\p{L}\p{N} &\/().,\'-]{2,60}$/u', $position)) {
            SalesPosition::create(['name' => $position, 'is_preset' => false, 'created_by' => $by->id]);
        }
    }

    /** Who already has this number in the pool ("Rohit (JK Cement)"), or null. */
    public static function numberOwner(string $normalized, ?string $exceptContactUuid = null, ?string $exceptClientUuid = null): ?string
    {
        $contact = SalesContact::query()->where('phone_normalized', $normalized)
            ->when($exceptContactUuid, fn ($q) => $q->where('uuid', '!=', $exceptContactUuid))
            ->first();

        if ($contact) {
            return $contact->client?->name ? $contact->name . ' (' . $contact->client->name . ')' : $contact->name;
        }

        $client = SalesClient::query()->where('phone_normalized', $normalized)
            ->when($exceptClientUuid, fn ($q) => $q->where('uuid', '!=', $exceptClientUuid))
            ->first();

        return $client?->name;
    }
}
