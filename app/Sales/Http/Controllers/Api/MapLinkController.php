<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Support\ApiError;
use App\Sales\Support\MapLinkResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * `POST /tools/resolve-map-link { "text": "<what the rep copied from Google Maps>" }` -> `{ "lat", "lng", "name" }`.
 * Only for signed-in reps; the work (and its safety rules) is in MapLinkResolver.
 */
class MapLinkController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['text' => ['required', 'string', 'min:5', 'max:1500']]);

        $found = MapLinkResolver::resolve($data['text']);

        if (! $found) {
            return ApiError::response('NOT_FOUND', 'Could not find the place in this link. Open the place in Google Maps and use Share > Copy link, or paste its numbers.', 404);
        }

        return response()->json(['lat' => $found['lat'], 'lng' => $found['lng'], 'name' => $found['name']]);
    }
}
