<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Models\SalesProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The product guide for the phones (read only: only a manager writes it, in the admin). The whole list is one answer of a few hundred
 * KB at most; the phone sends the `version` stamp it already holds and gets `{ unchanged: true }` back when nothing changed.
 */
class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['version' => ['nullable', 'string', 'max:40']]);

        $version = SalesProduct::version();

        if (($data['version'] ?? null) === $version) {
            return response()->json(['version' => $version, 'unchanged' => true]);
        }

        $products = SalesProduct::query()->where('is_active', true)->orderBy('sort')->orderBy('name')->get()
            ->map(fn (SalesProduct $p) => [
                'uuid' => $p->uuid,
                'name' => $p->name,
                'group' => $p->group ?? '',
                'sort' => (int) $p->sort,
                'premium_brands' => $p->premium_brands ?? [],
                'economy_brands' => $p->economy_brands ?? [],
                'lite_brands' => $p->lite_brands ?? [],
                'summary' => $p->summary ?? '',
                'variants' => $p->variants ?? [],
                'industries' => $p->industries ?? '',
                'key_parameters' => $p->key_parameters ?? [],
                'short_term_problems' => $p->short_term_problems ?? [],
                'long_term_problems' => $p->long_term_problems ?? [],
                'top_brands' => $p->top_brands ?? [],
                // "before the meeting": all empty until the manager writes them
                'client_types' => array_values(array_intersect(array_keys(SalesProduct::CLIENT_TYPES), $p->client_types ?? [])),
                'pitch' => $p->pitch ?? '',
                'key_notes' => $p->key_notes ?? [],
                'formula' => $p->formula ?? '',
                'do' => $p->do_list ?? [],
                'avoid' => $p->avoid_list ?? [],
                'match_rules' => $p->match_rules ?? [],
            ])
            ->values()
            ->all();

        return response()->json(['version' => $version, 'unchanged' => false, 'products' => $products]);
    }
}
