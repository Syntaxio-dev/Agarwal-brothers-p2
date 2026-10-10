<?php

namespace App\Support;

use App\Models\Vertical;

/** Data for the "where to go next" blocks on error pages and empty states. */
class HelpLinks
{
    /**
     * Top verticals (by their order in the admin panel). Never throws: error pages must still
     * render when the database is down, in which case the list is simply empty.
     *
     * @return list<array{name: string, url: string}>
     */
    public static function verticals(int $limit = 6): array
    {
        try {
            return Vertical::where('is_active', true)->orderBy('sort_order')->limit($limit)->get()
                ->map(fn ($v) => ['name' => $v->name, 'url' => route('vertical.show', $v->slug)])->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * People customers can call, from config/contact.php.
     *
     * @return list<array{title: string, phone: string, email: ?string}>
     */
    public static function contacts(): array
    {
        $cards = [];

        if ($call = config('contact.call')) {
            $cards[] = ['title' => 'Main line', 'phone' => $call, 'email' => config('contact.mail_24x7')];
        }

        foreach ((array) config('contact.departments', []) as $dept) {
            if (! empty($dept['phone'])) {
                $cards[] = ['title' => $dept['title'], 'phone' => $dept['phone'], 'email' => $dept['email'] ?? null];
            }
        }

        return $cards;
    }

    public static function hours(): ?string
    {
        return config('contact.working_days.0');
    }
}
