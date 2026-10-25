<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Models\SalesSyncBatch;
use App\Sales\Services\SyncService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    /** The phone sends what it created or changed (and what it deleted); the answer says what happened to each record. */
    public function push(Request $request): JsonResponse
    {
        $started = microtime(true);
        $max = (int) config('sales.max_push_records');

        $data = $request->validate([
            'app_version' => ['nullable', 'string', 'max:30'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'expenses' => ['sometimes', 'array', "max:$max"],
            'clients' => ['sometimes', 'array', "max:$max"],
            'meetings' => ['sometimes', 'array', "max:$max"],
            'contacts' => ['sometimes', 'array', "max:$max"],
            'calls' => ['sometimes', 'array', "max:$max"],
            'enquiries' => ['sometimes', 'array', "max:$max"],
            'reports' => ['sometimes', 'array', 'max:40'],
            'product_requests' => ['sometimes', 'array', 'max:40'],
            'positions' => ['sometimes', 'array', 'max:50'],
            'deleted' => ['sometimes', 'array'],
            'deleted.expenses' => ['sometimes', 'array', "max:$max"],
            'deleted.clients' => ['sometimes', 'array', "max:$max"],
            'deleted.contacts' => ['sometimes', 'array', "max:$max"],
            'deleted.meetings' => ['sometimes', 'array', "max:$max"],
            'deleted.enquiries' => ['sometimes', 'array', "max:$max"],
        ]);

        $user = $request->user();
        $outcome = $this->sync->push($user, $data);

        $this->touchProfile($request, $data);

        SalesSyncBatch::create([
            'user_id' => $user->id,
            'received' => $outcome['counts']['received'],
            'accepted' => $outcome['counts']['accepted'],
            'duplicates' => $outcome['counts']['duplicates'],
            'rejected' => $outcome['counts']['rejected'],
            'errors' => $outcome['samples'] ?: null,
            'app_version' => $data['app_version'] ?? null,
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
        ]);

        return response()->json([
            'results' => $outcome['results'],
            'server_time' => now()->utc()->format('Y-m-d\TH:i:s\Z'),
        ]);
    }

    /** Everything that changed since `since` (everything when it is missing): status changes, and a full restore on a new phone. */
    public function pull(Request $request): JsonResponse
    {
        $data = $request->validate(['since' => ['nullable', 'date']]);
        $since = filled($data['since'] ?? null) ? CarbonImmutable::parse($data['since'])->utc() : null;

        $profile = $request->user()->salesProfile;
        $payload = $this->sync->pull($request->user(), $since);

        $this->touchProfile($request, []);

        return response()->json($payload + [
            'account' => ['name' => $profile->user->name, 'employee_code' => $profile->employee_code, 'is_active' => true],
        ]);
    }

    private function touchProfile(Request $request, array $data): void
    {
        $profile = $request->user()->salesProfile;

        $profile->forceFill(array_filter([
            'last_sync_at' => now(),
            'app_version' => $data['app_version'] ?? $request->header('X-App-Version'),
            'device_model' => $data['device_model'] ?? null,
        ]))->save();
    }
}
