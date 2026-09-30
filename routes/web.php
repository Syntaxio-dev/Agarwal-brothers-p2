<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::view('/application-resources', 'public.placeholder', ['title' => 'Application Resources']);
Route::view('/careers', 'public.careers');
Route::view('/contact-us', 'public.contact-us');
Route::get('/insights/blogs', [PublicController::class, 'insights'])->defaults('type', 'blog')->name('insights.blogs');
Route::get('/insights/news-events', [PublicController::class, 'insights'])->defaults('type', 'news')->name('insights.news');
Route::get('/insights/webinars', [PublicController::class, 'insights'])->defaults('type', 'webinar')->name('insights.webinars');
Route::get('/insights/{insight:slug}', [PublicController::class, 'insightShow'])->name('insights.show');
Route::get('/verticals', [PublicController::class, 'verticalsIndex'])->name('verticals.index');

Route::get('/verticals/{vertical:slug}', [PublicController::class, 'vertical'])->name('vertical.show');
Route::get('/brands/{brand:slug}/{category:slug}', [PublicController::class, 'category'])->name('category.show');
Route::get('/products/{product:slug}', [PublicController::class, 'product'])->name('product.show');
Route::post('/enquiries', [PublicController::class, 'storeEnquiry'])->name('enquiry.store');
Route::get('/search', [PublicController::class, 'search'])->name('search');
