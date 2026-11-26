<?php

namespace App\Sales\Rep\Support;

use App\Models\User;
use App\Sales\Models\SalesProfile;

/**
 * What a rep may use on the web desk. A manager ticks the areas per rep (Sales team > Reps > profile > Web desk); nothing is open by
 * default. The check is made in three places, so a closed area stays closed even for a page that was already open in the browser:
 * the sign-in to the panel (no area = no desk), every request of a desk page (ChecksDeskArea), and every write (DeskSync).
 * Phone calls are never part of the desk.
 */
final class RepDesk
{
    /** key => [label, what it opens] (the keys are stored: never rename one) */
    public const AREAS = [
        'leads' => ['Leads and enquiries', 'See and update their own leads and enquiries: stage, follow-up, remarks.'],
        'prospects' => ['Prospects', 'Read the team\'s prospect pool, add prospects and their contact persons, log a call. Only the rep who added a prospect changes it.'],
        'meetings' => ['Meeting plans', 'Plan meetings and routes, move a meeting or mark it missed. A visit is still logged as done only on the phone (selfie at the place).'],
        'tasks' => ['Tasks', 'See the tasks you gave them, open the files and set Started / Done / Cannot do with a note.'],
        'notes' => ['To-do and notes', 'Their own private to-do list and notes.'],
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::AREAS);
    }

    /** @return array<string,string> key => label, for a checkbox list */
    public static function options(): array
    {
        return array_map(fn (array $a) => $a[0], self::AREAS);
    }

    /** @return array<string,string> key => what it opens */
    public static function descriptions(): array
    {
        return array_map(fn (array $a) => $a[1], self::AREAS);
    }

    /** Only known keys, each once, in the fixed order. @return list<string> */
    public static function clean(mixed $areas): array
    {
        $areas = is_array($areas) ? $areas : [];

        return array_values(array_filter(self::keys(), fn (string $k) => in_array($k, $areas, true)));
    }

    /** @return list<string> the areas open to this user (none for anybody who is not an active field rep) */
    public static function areasOf(?User $user): array
    {
        if (! $user || ! $user->is_active || $user->role !== SalesProfile::ROLE) {
            return [];
        }

        $profile = $user->salesProfile;
        if (! $profile || ! $profile->is_active) {
            return [];
        }

        return self::clean($profile->desk_areas);
    }

    public static function allows(?User $user, string $area): bool
    {
        return in_array($area, self::areasOf($user), true);
    }

    public static function mustAllow(?User $user, string $area): void
    {
        abort_unless(self::allows($user, $area), 403, 'Your manager has not opened this part of the web desk for you.');
    }
}
