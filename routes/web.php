<?php

use App\Http\Controllers\Admin\SlideAdminController;
use App\Http\Controllers\SlideFileController;
use App\Http\Controllers\StoreSlideController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');

Route::livewire('slides/{slide}', 'pages::slides.show')->name('slides.show');
Route::get('slides/{slide}/file', SlideFileController::class)->name('slides.file');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/admin/slides')->name('dashboard');

    Route::get('admin/slides', [SlideAdminController::class, 'index'])->name('admin.slides');
    Route::post('admin/slides', StoreSlideController::class)->name('admin.slides.store');
    Route::livewire('admin/categories', 'pages::admin.categories')->name('admin.categories');
});

require __DIR__.'/settings.php';
