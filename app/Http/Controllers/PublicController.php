<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryListRequest;
use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\NewEnquiry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\Slide;
use App\Models\Vertical;
use App\Support\FormRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PublicController extends Controller
{
    public function home()
    {
        $verticals = Vertical::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $slides = Slide::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $clients = Client::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $topPicks = Product::where('is_active', true)
            ->where('is_top_pick', true)
            ->whereHas('category.brand', fn ($q) => $q->where('is_active', true))
            ->with('category.brand')
            ->orderByRaw('top_pick_order IS NULL, top_pick_order ASC')
            ->limit(6)
            ->get();

        $brands = Brand::where('is_active', true)
            ->with('categories')
            ->orderBy('name')
            ->get();

        $blogs = Insight::where('is_active', true)
            ->where('type', 'blog')
            ->where('is_featured', true)
            ->latest()
            ->limit(4)
            ->get();

        if ($blogs->isEmpty()) {
            $blogs = Insight::where('is_active', true)
                ->where('type', 'blog')
                ->latest()
                ->limit(4)
                ->get();
        }

        $news = Insight::where('is_active', true)
            ->where('type', 'news')
            ->where('is_featured', true)
            ->latest()
            ->limit(3)
            ->get();

        if ($news->isEmpty()) {
            $news = Insight::where('is_active', true)
                ->where('type', 'news')
                ->latest()
                ->limit(3)
                ->get();
        }

        $featuredClients = Client::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(10)
            ->get();

        if ($featuredClients->isEmpty()) {
            $featuredClients = Client::where('is_active', true)->orderBy('sort_order')->limit(10)->get();
        }

        $reviews = Review::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        $mapCountries = Country::whereHas('brands', fn ($q) => $q->where('is_active', true))
            ->with(['brands' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'name' => $c->name,
                'lat' => $c->latitude,
                'lng' => $c->longitude,
                'brands' => $c->brands->map(fn ($b) => [
                    'name' => $b->name,
                    'logo' => $b->logo ? asset('storage/' . $b->logo) : null,
                    'url' => route('brand.show', $b->slug),
                ])->values(),
            ])->values();

        return view('public.home', compact(
            'verticals',
            'slides',
            'clients',
            'topPicks',
            'brands',
            'blogs',
            'news',
            'mapCountries',
            'featuredClients',
            'reviews',
        ));
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function verticalsIndex()
    {
        $verticals = Vertical::where('is_active', true)
            ->withCount('categories')
            ->orderBy('sort_order')
            ->get();

        return view('public.verticals-index', compact('verticals'));
    }

    public function vertical(Vertical $vertical)
    {
        abort_unless($vertical->is_active, 404);

        $categories = $vertical->categories()
            ->whereHas('brand', fn ($q) => $q->where('is_active', true))
            ->with('brand')
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Brand cards, each listing that brand's categories inside this vertical.
        $brandGroups = $categories->groupBy('brand_id')
            ->map(fn ($items) => ['brand' => $items->first()->brand, 'categories' => $items])
            ->sortBy(fn ($g) => $g['brand']->name)
            ->values();

        return view('public.vertical', compact('vertical', 'brandGroups'));
    }

    public function brand(Brand $brand)
    {
        abort_unless($brand->is_active, 404);

        $brand->load(['country', 'categories' => fn ($q) => $q->withCount([
            'products' => fn ($p) => $p->where('is_active', true),
        ])]);

        $products = Product::where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('brand_id', $brand->id))
            ->with('category.brand')
            ->get();

        return view('public.brand', compact('brand', 'products'));
    }

    public function category(Request $request, Brand $brand, Category $category)
    {
        abort_unless($brand->is_active && $category->brand_id === $brand->id, 404);

        $products = $category->products()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        // Already known: saves one query per card (used by the compare / enquiry-list buttons).
        $category->setRelation('brand', $brand);
        $products->each(fn ($p) => $p->setRelation('category', $category));

        // Group models (e.g. "Standard Models"), keeping first-seen order.
        $groups = $products->groupBy(fn ($p) => $p->model_group ?: 'Models');

        $vertical = $this->contextVertical($request, $category);

        return view('public.category', compact('brand', 'category', 'groups', 'products', 'vertical'));
    }

    public function product(Request $request, Product $product)
    {
        $product->load('category.brand');

        abort_unless($product->is_active && $product->category?->brand?->is_active, 404);

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        $vertical = $this->contextVertical($request, $product->category);

        return view('public.product', compact('product', 'related', 'vertical'));
    }

    /** The vertical the visitor came through (?v=slug), if it really contains this category. */
    private function contextVertical(Request $request, Category $category): ?Vertical
    {
        $slug = $request->query('v');

        if (! $slug) {
            return null;
        }

        return $category->verticals()->where('slug', $slug)->where('is_active', true)->first();
    }

    public function insights(Request $request, string $type)
    {
        $titles = [
            'blog' => 'Blogs',
            'news' => 'News & Events',
            'webinar' => 'Webinars',
        ];

        abort_unless(array_key_exists($type, $titles), 404);

        if ($type === 'webinar') {
            return $this->webinarsPage($request);
        }

        $date = 'COALESCE(event_date, DATE(created_at))';

        $month = (int) $request->input('month');
        $year = (int) $request->input('year');
        $upcoming = $type !== 'blog' && $request->boolean('upcoming');

        $insights = Insight::where('type', $type)
            ->where('is_active', true)
            ->when($month >= 1 && $month <= 12, fn ($q) => $q->whereRaw("MONTH($date) = ?", [$month]))
            ->when($year > 0, fn ($q) => $q->whereRaw("YEAR($date) = ?", [$year]))
            ->when($upcoming, fn ($q) => $q->whereDate('event_date', '>=', now(Insight::TZ)->toDateString()))
            ->orderByRaw("$date DESC")
            ->get();

        $years = Insight::where('type', $type)
            ->where('is_active', true)
            ->selectRaw("DISTINCT YEAR($date) as y")
            ->orderByDesc('y')
            ->pluck('y');

        $filters = [
            'month' => $month >= 1 && $month <= 12 ? $month : null,
            'year' => $year > 0 ? $year : null,
            'upcoming' => $upcoming,
        ];

        $heroField = ['blog' => 'blogs_hero', 'news' => 'news_hero', 'webinar' => 'webinars_hero'][$type];
        $heroPath = optional(\App\Models\SiteSetting::latest()->first())->{$heroField};

        return view('public.insights', [
            'insights' => $insights,
            'title' => $titles[$type],
            'type' => $type,
            'years' => $years,
            'filters' => $filters,
            'hasFilters' => (bool) array_filter($filters),
            'hero' => $heroPath ? asset('storage/' . $heroPath) : null,
        ]);
    }

    private function webinarsPage(Request $request)
    {
        $all = Insight::with('brand')
            ->where('type', 'webinar')
            ->where('is_active', true)
            ->get();

        // Principals that have at least one webinar, with counts, for the filter chips.
        $principals = $all->filter(fn ($w) => $w->brand)
            ->groupBy('brand_id')
            ->map(fn ($group) => ['brand' => $group->first()->brand, 'count' => $group->count()])
            ->sortBy(fn ($row) => $row['brand']->name)
            ->values();

        $selected = $principals->firstWhere(fn ($row) => $row['brand']->slug === $request->input('principal'));
        $selectedSlug = $selected ? $selected['brand']->slug : null;

        $shown = $selected ? $all->where('brand_id', $selected['brand']->id) : $all;

        $upcoming = $shown->filter(fn ($w) => $w->isUpcoming())->sortBy('starts_at')->values();
        $past = $shown->reject(fn ($w) => $w->isUpcoming())
            ->sortByDesc(fn ($w) => $w->starts_at ?? $w->created_at)
            ->values();

        // The hero always features the next webinar overall, regardless of the filter.
        $next = $all->filter(fn ($w) => $w->isUpcoming())->sortBy('starts_at')->first();

        $heroPath = optional(\App\Models\SiteSetting::latest()->first())->webinars_hero;

        return view('public.webinars', [
            'title' => 'Webinars',
            'next' => $next,
            'principals' => $principals,
            'totalCount' => $all->count(),
            'selectedSlug' => $selectedSlug,
            'upcoming' => $upcoming,
            'past' => $past,
            'hero' => $heroPath ? asset('storage/' . $heroPath) : null,
        ]);
    }

    public function ourStory()
    {
        $settings = SiteSetting::current();

        return view('public.our-story', [
            's' => $settings,
            'years' => max(1, now()->year - ($settings?->founded_year ?: 1981)),
            'leaders' => TeamMember::where('is_active', true)->where('is_leader', true)->orderBy('sort_order')->orderBy('id')->get(),
            'team' => TeamMember::where('is_active', true)->where('is_leader', false)->orderBy('sort_order')->orderBy('id')->get(),
            'reviews' => Review::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function applicationResources()
    {
        $resources = \App\Models\ApplicationResource::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('public.application-resources', compact('resources'));
    }

    public function insightShow(Insight $insight)
    {
        abort_unless($insight->is_active, 404);

        $insight->load('brand');

        $related = Insight::where('type', $insight->type)
            ->where('is_active', true)
            ->where('id', '!=', $insight->id)
            ->latest()
            ->limit(3)
            ->get();

        return view('public.insight-show', compact('insight', 'related'));
    }

    /** Side-by-side specification table for up to four products (?p=slug1,slug2). */
    public function compare(Request $request)
    {
        $max = 4;

        $slugs = collect(explode(',', mb_substr((string) $request->query('p', ''), 0, 400)))
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => preg_match('/^[A-Za-z0-9-]{1,190}$/', $s))
            ->unique()
            ->take($max)
            ->values();

        $found = Product::whereIn('slug', $slugs)
            ->where('is_active', true)
            ->whereHas('category.brand', fn ($q) => $q->where('is_active', true))
            ->with('category.brand')
            ->get()
            ->keyBy('slug');

        $products = $slugs->map(fn ($s) => $found->get($s))->filter()->values();

        // One row per specification name (matched ignoring case and spaces), in order of first appearance.
        $rows = [];
        foreach ($products as $i => $product) {
            foreach ((array) ($product->specs ?? []) as $label => $value) {
                $label = trim((string) $label);
                $value = is_scalar($value) ? trim((string) $value) : '';
                if ($label === '' || $value === '') {
                    continue;
                }
                $key = mb_strtolower(preg_replace('/\s+/', ' ', $label));
                $rows[$key]['label'] ??= $label;
                $rows[$key]['cells'][$i] = $value;
            }
        }

        $rows = collect($rows)->map(function ($row) use ($products) {
            $cells = [];
            foreach ($products as $i => $_) {
                $cells[$i] = $row['cells'][$i] ?? null;
            }
            $distinct = collect($cells)->map(fn ($c) => $c === null ? null : mb_strtolower($c))->unique();
            $row['cells'] = $cells;
            $row['differs'] = $distinct->count() > 1;

            return $row;
        })->values();

        return view('public.compare', compact('products', 'rows', 'max'));
    }

    /**
     * Shared catalogue search (full results page and live suggestions).
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection, 2: \Illuminate\Support\Collection, 3: \Illuminate\Support\Collection} products, brands, product lines, verticals
     */
    private function catalogueSearch(string $query, int $limitProducts, int $limitBrands, int $limitLines, int $limitVerticals): array
    {
        // Escape LIKE wildcards so "50%" or "a_b" are searched literally.
        $escaped = addcslashes($query, '\\%_');
        $contains = "%{$escaped}%";
        $starts = "{$escaped}%";

        $products = Product::where('products.is_active', true)
            ->whereHas('category.brand', fn ($q) => $q->where('brands.is_active', true))
            ->where(function ($q) use ($contains) {
                $q->where('products.name', 'like', $contains)
                    ->orWhere('products.short_description', 'like', $contains)
                    ->orWhereHas('category', fn ($c) => $c->where('categories.name', 'like', $contains))
                    ->orWhereHas('category.brand', fn ($b) => $b->where('brands.name', 'like', $contains));
            })
            ->with('category.brand')
            ->orderByRaw('CASE WHEN products.name LIKE ? THEN 1 WHEN products.name LIKE ? THEN 2 ELSE 3 END', [$starts, $contains])
            ->orderBy('products.name')
            ->limit($limitProducts)
            ->get();

        $brands = Brand::where('is_active', true)
            ->where('name', 'like', $contains)
            ->withCount('categories')
            ->orderBy('name')
            ->limit($limitBrands)
            ->get();

        $lines = Category::whereHas('brand', fn ($b) => $b->where('is_active', true))
            ->where('name', 'like', $contains)
            ->with('brand')
            ->orderBy('name')
            ->limit($limitLines)
            ->get();

        $verticals = Vertical::where('is_active', true)
            ->where('name', 'like', $contains)
            ->orderBy('sort_order')
            ->limit($limitVerticals)
            ->get();

        return [$products, $brands, $lines, $verticals];
    }

    /** Live search suggestions for the search boxes (JSON). */
    public function suggest(Request $request)
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 80);
        $rows = [];

        if (mb_strlen($query) >= 2) {
            [$products, $brands, $lines, $verticals] = $this->catalogueSearch($query, 5, 3, 3, 2);

            foreach ($products as $p) {
                $rows[] = ['type' => 'Products', 'label' => $p->name, 'sub' => collect([$p->category?->brand?->name, $p->category?->name])->filter()->implode(' · '),
                    'url' => route('product.show', $p->slug), 'image' => $p->image ? asset('storage/' . $p->image) : null];
            }
            foreach ($brands as $b) {
                $rows[] = ['type' => 'Brands', 'label' => $b->name, 'sub' => $b->categories_count . ' product ' . \Illuminate\Support\Str::plural('line', $b->categories_count),
                    'url' => route('brand.show', $b->slug), 'image' => $b->logo ? asset('storage/' . $b->logo) : null];
            }
            foreach ($lines as $c) {
                $rows[] = ['type' => 'Product lines', 'label' => $c->name, 'sub' => $c->brand?->name,
                    'url' => route('category.show', [$c->brand->slug, $c->slug]), 'image' => $c->image ? asset('storage/' . $c->image) : null];
            }
            foreach ($verticals as $v) {
                $rows[] = ['type' => 'Verticals', 'label' => $v->name, 'sub' => null,
                    'url' => route('vertical.show', $v->slug), 'image' => null];
            }
        }

        return response()->json(['q' => $query, 'rows' => $rows]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $query = mb_substr($query, 0, 80);

        $products = collect();
        $brands = collect();
        $lines = collect();
        $verticals = collect();

        if ($query !== '') {
            [$products, $brands, $lines, $verticals] = $this->catalogueSearch($query, 60, 8, 10, 6);
        }

        // Shown when there is no query or nothing matched.
        $suggestVerticals = Vertical::where('is_active', true)->orderBy('sort_order')->limit(8)->get();
        $suggestBrands = Brand::where('is_active', true)->orderBy('name')->limit(10)->get();

        return view('public.search', compact(
            'products', 'brands', 'lines', 'verticals', 'query', 'suggestVerticals', 'suggestBrands'
        ));
    }

    /** The visitor's enquiry list (items live in their browser; this page just renders the form). */
    public function enquiryList()
    {
        return view('public.enquiry-list');
    }

    /** One enquiry for several products. Product details are re-read from the database, never trusted from the form. */
    public function storeEnquiryList(StoreEnquiryListRequest $request)
    {
        $data = FormRules::finish($request->validated());

        $quantities = collect($data['items'])->mapWithKeys(fn ($i) => [$i['slug'] => (int) $i['qty']]);

        $products = Product::whereIn('slug', $quantities->keys())
            ->where('is_active', true)
            ->whereHas('category.brand', fn ($q) => $q->where('brands.is_active', true))
            ->with('category.brand')
            ->get();

        if ($products->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'These products are no longer available. Please add them again.']);
        }

        $enquiry = DB::transaction(function () use ($data, $products, $quantities) {
            $enquiry = Enquiry::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'order_location' => $data['order_location'] ?? null,
                'message' => $data['message'] ?? null,
            ]);

            foreach ($products as $product) {
                $enquiry->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'brand_name' => $product->category?->brand?->name,
                    'category_name' => $product->category?->name,
                    'quantity' => $quantities[$product->slug] ?? 1,
                ]);
            }

            return $enquiry;
        });

        try {
            Mail::to(config('contact.inbox') ?: config('mail.from.address'))
                ->queue(new NewEnquiry($enquiry));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('enquiry-list')->with('enquiry_sent', $products->count());
    }

    public function storeEnquiry(StoreEnquiryRequest $request)
    {
        $enquiry = Enquiry::create($request->validated());

        try {
            Mail::to(config('contact.inbox') ?: config('mail.from.address'))
                ->queue(new NewEnquiry($enquiry));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Thank you! We have received your enquiry and will contact you soon.')->withFragment('enquiry-form');
    }
}
