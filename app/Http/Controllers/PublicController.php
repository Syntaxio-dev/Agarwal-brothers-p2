<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\NewEnquiry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\Product;
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
            ->get();

        $brands = Brand::where('is_active', true)
            ->with('categories')
            ->orderBy('name')
            ->get();

        return view('public.home', compact(
            'verticals',
            'slides',
            'clients',
            'topPicks',
            'brands',
        ));
    }

    public function verticalsIndex()
    {
        $verticals = Vertical::where('is_active', true)
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

        $insights = Insight::where('type', $type)
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('public.insights', [
            'insights' => $insights,
            'title' => $titles[$type],
        ]);
    }

    public function insightShow(Insight $insight)
    {
        abort_unless($insight->is_active, 404);

        return view('public.insight-show', compact('insight'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->with('category.brand')
            ->limit(50)
            ->get();

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
