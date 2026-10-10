@extends('errors.layout')

@section('title', 'Page not found')
@section('eyebrow', 'Error 404')
@section('code', '404')
@section('heading', 'This page could not be found')
@section('message', 'The page you are looking for may have moved, been renamed, or never existed. Try searching for the product or instrument you need.')

@section('extra')
    <form action="/search" method="GET" role="search" style="animation-delay: 380ms" class="intro-rise mx-auto mt-8 flex max-w-md items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm focus-within:border-cyan focus-within:ring-2 focus-within:ring-cyan/20">
        <svg class="ml-3 h-5 w-5 shrink-0 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Search products, brands, categories" aria-label="Search"
               class="min-w-0 flex-1 bg-transparent px-1 py-2 text-sm text-navy placeholder:text-slate outline-none">
        <button type="submit" class="btn-primary shrink-0 rounded-lg px-5 py-2 text-sm font-semibold text-white">Search</button>
    </form>
@endsection
