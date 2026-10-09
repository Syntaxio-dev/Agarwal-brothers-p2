<?php

namespace App\Http\Controllers;

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
use App\Models\Slide;
use App\Models\Vertical;
use Illuminate\Http\Request;
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

        $vertical->load(['categories' => function ($q) {
            $q->orderBy('sort_order');
        }, 'categories.brand']);

        return view('public.vertical', compact('vertical'));
    }

    public function brand(Brand $brand)
    {
        abort_unless($brand->is_active, 404);

        $brand->load(['country', 'categories' => fn ($q) => $q->withCount([
            'products' => fn ($p) => $p->where('is_active', true),
        ])]);

        $products = Product::where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('brand_id', $brand->id))
            ->with('category')
            ->get();

        return view('public.brand', compact('brand', 'products'));
    }

    public function category(Brand $brand, Category $category)
    {
        abort_unless($brand->is_active, 404);

        $category->load(['products' => function ($q) {
            $q->where('is_active', true);
        }]);

        return view('public.category', compact('brand', 'category'));
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load('category.brand');

        return view('public.product', compact('product'));
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
            ->when($upcoming, fn ($q) => $q->whereDate('event_date', '>=', today()))
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

        return view('public.insight-show', compact('insight'));
    }

    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        $products = collect();

        if (strlen($query) >= 1) {
            $products = Product::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('products.name', 'like', "%{$query}%")
                        ->orWhere('products.short_description', 'like', "%{$query}%")
                        ->orWhereHas('category', function ($catQ) use ($query) {
                            $catQ->where('categories.name', 'like', "%{$query}%");
                        })
                        ->orWhereHas('category.brand', function ($brandQ) use ($query) {
                            $brandQ->where('brands.name', 'like', "%{$query}%");
                        });
                })
                ->with('category.brand')
                ->orderByRaw("
                    CASE
                        WHEN products.name LIKE ? THEN 1
                        WHEN products.name LIKE ? THEN 2
                        ELSE 3
                    END
                ", ["{$query}%", "%{$query}%"])
                ->limit(60)
                ->get();
        }

        return view('public.search', compact('products', 'query'));
    }

    public function storeEnquiry(StoreEnquiryRequest $request)
    {
        $enquiry = Enquiry::create($request->validated());

        try {
            Mail::to(config('mail.from.address'))
                ->send(new NewEnquiry($enquiry));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Thank you! We have received your enquiry and will contact you soon.');
    }
}
