<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('portfolios.create'))->name('home');
Route::get('/up', HealthController::class)->name('health');

Route::get('/portfolios/create', [PortfolioController::class, 'create'])->name('portfolios.create');
Route::view('/help', 'help')->name('help');
if (app()->environment(['local', 'testing'])) {
    Route::get('/__local-errors/{status}', fn (string $status) => abort((int) $status))
        ->whereIn('status', ['403', '404', '419', '429', '500']);
}
Route::post('/store', [PortfolioController::class, 'store'])->middleware('throttle:20,1')->name('portfolios.store');

Route::get('/portfolio/{portfolio:slug}/select-template', [PortfolioController::class, 'selectTemplate'])
    ->middleware('signed')
    ->name('portfolio.select-template');
Route::post('/portfolio/{portfolio:slug}/save-template', [PortfolioController::class, 'saveTemplate'])
    ->middleware(['signed', 'throttle:20,1'])
    ->name('portfolio.save-template');
Route::get('/p/{portfolio:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

Route::middleware('signed')->group(function () {
    Route::get('/manage/{portfolio:slug}', [PortfolioController::class, 'manage'])->name('manage.show');
    Route::get('/manage/{portfolio:slug}/edit', [PortfolioController::class, 'edit'])->name('manage.edit');
    Route::get('/manage/{portfolio:slug}/deleted', [PortfolioController::class, 'deleted'])
        ->withTrashed()
        ->name('manage.deleted');

    Route::put('/manage/{portfolio:slug}', [PortfolioController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('manage.update');
    Route::delete('/manage/{portfolio:slug}', [PortfolioController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->name('manage.destroy');
    Route::post('/manage/{portfolio:slug}/undo', [PortfolioController::class, 'undo'])
        ->withTrashed()
        ->middleware('throttle:20,1')
        ->name('manage.undo');
    Route::delete('/manage/{portfolio:slug}/purge', [PortfolioController::class, 'purge'])
        ->withTrashed()
        ->middleware('throttle:20,1')
        ->name('manage.purge');
});
