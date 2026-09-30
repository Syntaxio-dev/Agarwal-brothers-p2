<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreEnquiryRequest;

class PublicController extends Controller
{
    public function vertical(\App\Models\Vertical $vertical)
{
    $vertical->load('categories.brand');
    return view('public.vertical', compact('vertical'));
}

public function category(\App\Models\Brand $brand, \App\Models\Category $category)
{
    $category->load('products');
    return view('public.category', compact('brand', 'category'));
}

public function product(\App\Models\Product $product)
{
    $product->load('category.brand');
    return view('public.product', compact('product'));
}
public function storeEnquiry(\App\Http\Requests\StoreEnquiryRequest $request)
{
    $enquiry = \App\Models\Enquiry::create($request->validated());

    \Illuminate\Support\Facades\Mail::to(config('mail.from.address'))
        ->send(new \App\Mail\NewEnquiry($enquiry));

    return back()->with('success', 'Thank you! We have received your enquiry and will contact you soon.');
}
public function verticalsIndex()
{
    $verticals = \App\Models\Vertical::where('is_active', true)->orderBy('sort_order')->get();
    return view('public.verticals-index', compact('verticals'));
}
public function insights(\Illuminate\Http\Request $request, string $type)
{
    $insights = \App\Models\Insight::where('type', $type)->where('is_active', true)->latest()->get();
    $titles = ['blog' => 'Blogs', 'news' => 'News & Events', 'webinar' => 'Webinars'];
    return view('public.insights', ['insights' => $insights, 'title' => $titles[$type]]);
}

public function insightShow(\App\Models\Insight $insight)
{
    return view('public.insight-show', compact('insight'));
}
public function search(\Illuminate\Http\Request $request)
{
    $query = $request->input('q');

    $products = \App\Models\Product::where('name', 'like', "%{$query}%")
        ->orWhere('short_description', 'like', "%{$query}%")
        ->where('is_active', true)
        ->with('category.brand')
        ->get();

    return view('public.search', compact('products', 'query'));
}
public function home()
{
    $verticals = \App\Models\Vertical::where('is_active', true)->orderBy('sort_order')->get();
    $slides = \App\Models\Slide::where('is_active', true)->orderBy('sort_order')->get();
    $clients = \App\Models\Client::where('is_active', true)->orderBy('sort_order')->get();
return view('public.home', compact('verticals', 'slides', 'clients'));}
}
