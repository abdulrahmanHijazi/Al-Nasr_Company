<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Storefront (Inertia + Vue + TS)
Route::get('/', [\App\Http\Controllers\StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [\App\Http\Controllers\StorefrontController::class, 'shop'])->name('shop');
Route::get('/products/{product:slug}', [\App\Http\Controllers\StorefrontController::class, 'showProduct'])->name('products.show');
Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [\App\Http\Controllers\BrandController::class, 'show'])->name('brands.show');
Route::get('/categories/{category:slug}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');
Route::get('/portfolio', [\App\Http\Controllers\PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{item:slug}', [\App\Http\Controllers\PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/offers', [\App\Http\Controllers\OfferController::class, 'index'])->name('offers.index');
Route::get('/offers/{bundle:slug}', [\App\Http\Controllers\OfferController::class, 'show'])->name('offers.show');
Route::get('/contact', [\App\Http\Controllers\ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

require __DIR__.'/dashboard.php';
