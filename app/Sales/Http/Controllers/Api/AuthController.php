<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\ApiError;
use App\Sales\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Same text for "no such rep" and "wrong password", so the answer does not reveal who exists. */
    private const BAD_LOGIN = 'Employee code / phone or password is wrong.';

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:200'],
            'app_version' => ['nullable', 'string', 'max:30'],
            'device_model' => ['nullable', 'string', 'max:100'],
        ]);

        $profile = $this->find($data['login']);

        // Always do one hash of the same cost, so a missing rep takes as long to answer as a wrong password.
        $valid = Hash::check($data['password'], $profile?->user?->password ?? Hash::make(Str::random(24)));

        if (! $profile || ! $valid) {
            return ApiError::response('INVALID_CREDENTIALS', self::BAD_LOGIN, 401);
        }

        if (! $profile->canUseApp()) {
            return ApiError::response('ACCOUNT_INACTIVE', 'Your account is switched off. Please contact your manager.', 403);
        }

        // One phone at a time: signing in on a new phone signs the old one out.
        $profile->user->tokens()->delete();

        $expires = now()->addDays((int) config('sales.token_days'));
        $token = $profile->user->createToken('sales-app', ['sales:rep'], $expires);

        $profile->forceFill(array_filter([
            'app_version' => $data['app_version'] ?? null,
            'device_model' => $data['device_model'] ?? null,
        ]))->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expires->utc()->format('Y-m-d\TH:i:s\Z'),
            'account' => $this->account($profile),
            'server_time' => now()->utc()->format('Y-m-d\TH:i:s\Z'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'account' => $this->account($request->user()->salesProfile),
            'server_time' => now()->utc()->format('Y-m-d\TH:i:s\Z'),
        ]);
    }

    private function find(string $login): ?SalesProfile
    {
        $login = trim($login);
        $phone = PhoneNumber::normalize($login);

        return SalesProfile::query()
            ->with('user')
            ->where(function ($query) use ($login, $phone) {
                $query->where('employee_code', Str::upper($login));

                if ($phone) {
                    $query->orWhere('phone', $phone);
                }
            })
            ->first();
    }

    private function account(SalesProfile $profile): array
    {
        return [
            'name' => $profile->user->name,
            'employee_code' => $profile->employee_code,
            'phone' => $profile->phone,
        ];
    }
}
