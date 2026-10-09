<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PublicController;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::view('/our-story', 'public.our-story');
Route::get('/application-resources', [PublicController::class, 'applicationResources'])->name('application-resources');
Route::get('/careers', [CareerController::class, 'index'])->name('careers');
Route::post('/careers/apply', [CareerController::class, 'applyGeneral'])->name('careers.apply.general')->middleware('throttle:5,1');
Route::get('/careers/{opening:slug}', [CareerController::class, 'show'])->name('careers.show');
Route::post('/careers/{opening:slug}/apply', [CareerController::class, 'applyToOpening'])->name('careers.apply')->middleware('throttle:5,1');
Route::get('/contact-us', [ContactController::class, 'show'])->name('contact');
Route::post('/contact-us', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');
Route::get('/insights/blogs', [PublicController::class, 'insights'])->defaults('type', 'blog')->name('insights.blogs');
Route::get('/insights/news-events', [PublicController::class, 'insights'])->defaults('type', 'news')->name('insights.news');
Route::get('/insights/webinars', [PublicController::class, 'insights'])->defaults('type', 'webinar')->name('insights.webinars');
Route::get('/insights/{insight:slug}', [PublicController::class, 'insightShow'])->name('insights.show');
Route::get('/verticals', [PublicController::class, 'verticalsIndex'])->name('verticals.index');

Route::get('/verticals/{vertical:slug}', [PublicController::class, 'vertical'])->name('vertical.show');
Route::get('/brands/{brand:slug}', [PublicController::class, 'brand'])->name('brand.show');
Route::get('/brands/{brand:slug}/{category:slug}', [PublicController::class, 'category'])->name('category.show');
Route::get('/products/{product:slug}', [PublicController::class, 'product'])->name('product.show');
Route::post('/enquiries', [PublicController::class, 'storeEnquiry'])->name('enquiry.store')->middleware('throttle:5,1');
Route::get('/search', [PublicController::class, 'search'])->name('search');
