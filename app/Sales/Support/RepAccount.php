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
                'email' => Str::lower($code) . '@' . config('sales.rep_email_domain'),
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
}
