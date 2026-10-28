<?php

namespace App\Sales\Support;

use App\Models\User;
use App\Sales\Models\SalesProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creating and switching field reps. Reps are made only here (by a manager in the admin, or a seeder): there is no sign-up.
 * A rep is a normal `users` row with role `field_rep`. That role is not listed in config/roles.php on purpose,
 * so User::isStaff() is false and the rep can never open the admin panel.
 */
class RepAccount
{
    /**
     * @param  array{name: string, employee_code: string, phone?: ?string, password: string, is_active?: bool}  $data
     */
    public static function create(array $data): SalesProfile
    {
        return DB::transaction(function () use ($data) {
            $code = Str::upper(trim($data['employee_code']));

            $user = User::create([
                'name' => $data['name'],
                'email' => static::emailFor($code, $data['email'] ?? null),
                'password' => $data['password'],
                'role' => SalesProfile::ROLE,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return SalesProfile::create([
                'user_id' => $user->id,
                'employee_code' => $code,
                'phone' => PhoneNumber::normalize($data['phone'] ?? null),
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /** The rep's real e-mail when the manager typed one, else a placeholder made from the code (users.email must be unique and filled). */
    public static function emailFor(string $code, ?string $email): string
    {
        return filled($email) ? Str::lower(trim($email)) : Str::lower($code) . '@' . config('sales.rep_email_domain');
    }

    /** The e-mail to show: nothing for a placeholder. */
    public static function realEmail(?string $email): ?string
    {
        return $email && ! str_ends_with($email, '@' . config('sales.rep_email_domain')) ? $email : null;
    }

    /** A password a manager can read out or send: 10 letters and digits without look-alikes (no 0/O, 1/l/I). */
    public static function newPassword(): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $out = '';

        for ($i = 0; $i < 10; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $out;
    }

    /** Switch a rep on or off. Off = every app token is revoked at once, so the phone is signed out on its next call. */
    public static function setActive(SalesProfile $profile, bool $active): void
    {
        DB::transaction(function () use ($profile, $active) {
            $profile->update(['is_active' => $active]);
            $profile->user->update(['is_active' => $active]);

            if (! $active) {
                $profile->user->tokens()->delete();
            }
        });
    }

    public static function resetPassword(SalesProfile $profile, string $password): void
    {
        $profile->user->update(['password' => $password]);
        $profile->user->tokens()->delete();   // every phone signs in again with the new password
    }

    /** Every kind of record a rep owns, by kind, as a number (what a delete would remove). Soft-deleted rows count too. */
    public static function recordCounts(SalesProfile $profile): array
    {
        $uid = $profile->user_id;
        $count = fn (string $model) => (int) (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model)) ? $model::withTrashed() : $model::query())->where('user_id', $uid)->count();

        return [
            'expenses' => $count(\App\Sales\Models\SalesExpense::class),
            'meetings' => $count(\App\Sales\Models\SalesMeeting::class),
            'routes' => $count(\App\Sales\Models\SalesRoute::class),
            'enquiries' => $count(\App\Sales\Models\SalesEnquiry::class),
            'reports' => $count(\App\Sales\Models\SalesDailyReport::class),
            'calls' => $count(\App\Sales\Models\SalesCall::class),
            'scripts' => $count(\App\Sales\Models\SalesScript::class),
            'requests' => $count(\App\Sales\Models\SalesProductRequest::class),
        ];
    }

    /**
     * Delete a rep AND everything they own, for good (a manager asked for it; the screens ask first and only offer it for a rep who is switched
     * off). The shared client pool stays: the clients and people this rep added now belong to the manager who deletes. Bill photos go too.
     * Written to the activity log first (with the counts). Returns what was removed.
     */
    public static function deleteWithRecords(SalesProfile $profile, User $by): array
    {
        abort_if($profile->is_active, 422, 'Switch the rep off first.');
        abort_if((int) $profile->user_id === (int) $by->id, 422, 'You cannot delete your own account.');
        abort_unless($profile->user?->role === SalesProfile::ROLE, 422, 'Only a field rep can be deleted here.');

        $counts = static::recordCounts($profile);
        $uid = $profile->user_id;
        $paths = \App\Sales\Models\SalesExpense::withTrashed()->where('user_id', $uid)->whereNotNull('bill_path')->pluck('bill_path')->all();

        DB::transaction(function () use ($profile, $by, $uid, $counts) {
            \App\Support\ActivityLogger::record('deleted', $profile, ['records' => ['old' => collect($counts)->map(fn ($n, $k) => $n . ' ' . $k)->implode(', '), 'new' => null]], $by);

            foreach ([\App\Sales\Models\SalesExpense::class, \App\Sales\Models\SalesMeeting::class, \App\Sales\Models\SalesRoute::class, \App\Sales\Models\SalesEnquiry::class, \App\Sales\Models\SalesScript::class] as $model) {
                $model::withTrashed()->where('user_id', $uid)->forceDelete();
            }
            \App\Sales\Models\SalesDailyReport::where('user_id', $uid)->delete();
            \App\Sales\Models\SalesCall::where('user_id', $uid)->delete();
            \App\Sales\Models\SalesProductRequest::where('user_id', $uid)->delete();

            // the shared pool survives: what this rep added is handed to the manager
            \App\Sales\Models\SalesClient::withTrashed()->where('user_id', $uid)->update(['user_id' => $by->id]);
            \App\Sales\Models\SalesContact::withTrashed()->where('user_id', $uid)->update(['user_id' => $by->id]);

            $user = $profile->user;
            $user->tokens()->delete();
            $profile->delete();
            $user->delete();
        });

        // the photos go only once the database part has committed: a failure above must not leave expenses without their photo
        foreach ($paths as $path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
        }
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory("sales/bills/{$uid}");

        return $counts;
    }
}
