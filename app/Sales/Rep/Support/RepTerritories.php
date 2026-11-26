<?php

namespace App\Sales\Rep\Support;

use App\Models\User;
use App\Sales\Models\SalesProspect;
use Illuminate\Validation\ValidationException;

/**
 * Territories on the web desk, the same idea as the app: the team has ONE spelling per territory (the first one saved), and every rep
 * keeps a short list of "My territories" (up to 20) that the Prospects screen shows first. A territory is written like a place:
 * letters, spaces and . , ' - (the app's rules.place).
 */
final class RepTerritories
{
    public const MAX_MINE = 20;

    /** The app's quick picks, after the team's own territories (core/prospect.js SUGGESTED_TERRITORIES). */
    public const SUGGESTED = ['Sitapura', 'Sanganer', 'Jhotwara', 'Bagru', 'Kukas', 'Chomu', 'Dausa', 'Behror', 'Bhiwadi', 'Neemrana', 'Phulera', 'Kishangarh'];

    /** The app's PLACE_NAME: a letter first, then letters, marks, spaces and . , ' - */
    public const PATTERN = "/^\\p{L}[\\p{L}\\p{M} .,'-]*$/u";

    public static function clean(?string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $name) ?? '');
    }

    public static function problem(?string $name): ?string
    {
        $name = self::clean($name);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            return 'A territory has 2 to 100 letters.';
        }

        return preg_match(self::PATTERN, $name) ? null : "A territory uses letters, spaces and . , ' - only.";
    }

    /** @return array<string,int> the team's territories (one spelling each) => how many prospects, A to Z */
    public static function team(): array
    {
        return SalesProspect::query()
            ->selectRaw('MIN(territory) as name, COUNT(*) as n')
            ->groupBy('territory_key')
            ->orderBy('name')
            ->pluck('n', 'name')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /** The team's spelling of a territory, or the typed one cleaned when the team does not have it yet. */
    public static function canonical(string $typed): string
    {
        return DeskProspects::territory(self::clean($typed));
    }

    /** @return list<string> */
    public static function mine(?User $user): array
    {
        return self::cleanList($user?->salesProfile?->desk_territories);
    }

    /** @param list<string> $names */
    public static function save(User $user, array $names): array
    {
        foreach ($names as $name) {
            if ($problem = self::problem($name)) {
                throw ValidationException::withMessages(['territories' => $problem]);
            }
        }

        $list = self::cleanList(array_map(fn ($n) => self::canonical((string) $n), $names));
        if (count($list) > self::MAX_MINE) {
            throw ValidationException::withMessages(['territories' => 'Keep at most ' . self::MAX_MINE . ' territories.']);
        }

        $user->salesProfile?->update(['desk_territories' => $list ?: null]);

        return $list;
    }

    /** Add one territory to the rep's list (after a prospect was saved there), when there is room. */
    public static function remember(User $user, string $territory): void
    {
        $mine = self::mine($user);
        $key = SalesProspect::keyOf($territory);
        if (count($mine) >= self::MAX_MINE || collect($mine)->contains(fn ($m) => SalesProspect::keyOf($m) === $key)) {
            return;
        }

        $user->salesProfile?->update(['desk_territories' => [...$mine, $territory]]);
    }

    /**
     * The capsules of the territory picker: my territories first, then the team's, then the app's suggestions; one spelling each.
     * @return array<string,string> name => name
     */
    public static function picks(?User $user): array
    {
        $out = [];
        foreach ([...self::mine($user), ...array_keys(self::team()), ...self::SUGGESTED] as $name) {
            $key = SalesProspect::keyOf($name);
            if ($key !== '' && ! isset($out[$key])) {
                $out[$key] = $name;
            }
        }

        return array_combine(array_values($out), array_values($out)) ?: [];
    }

    /** @return list<string> valid, one spelling each (by key), in order, at most MAX_MINE */
    private static function cleanList(mixed $list): array
    {
        $out = [];
        foreach (is_array($list) ? $list : [] as $raw) {
            $name = self::clean(is_string($raw) ? $raw : '');
            $key = SalesProspect::keyOf($name);
            if (self::problem($name) === null && ! isset($out[$key])) {
                $out[$key] = $name;
            }
        }

        return array_slice(array_values($out), 0, self::MAX_MINE);
    }
}
