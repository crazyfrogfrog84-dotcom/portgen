<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PortfolioImageController;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/', fn () => redirect()->route('portfolios.create'))->name('home');
Route::get('/up', HealthController::class)->name('health');

Route::get('/portfolios/create', [PortfolioController::class, 'create'])->name('portfolios.create');
Route::view('/help', 'help')->name('help');
Route::middleware(['guest', 'auth.no-store'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1,register')->name('register.store');

});
Route::middleware(['auth', 'auth.no-store'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/my-portfolios', DashboardController::class)->name('dashboard');
    Route::post('/manage/{portfolio:slug}/claim', [PortfolioController::class, 'claim'])
        ->middleware('portfolio.owner')->name('manage.claim');
});
if (app()->environment(['local', 'testing'])) {
    Route::get('/__local-errors/{status}', fn (string $status) => abort((int) $status))
        ->whereIn('status', ['403', '404', '419', '429', '500']);
    Route::get('/__local/simple-fixture/{fixture}', function (string $fixture) {
        $slugs = ['demo-minimal' => 'demo-min', 'demo-rich' => 'demo-rich', 'demo-stress' => 'demo-stres'];
        abort_unless(isset($slugs[$fixture]), 404);
        $portfolio = Portfolio::query()->where('slug', $slugs[$fixture])->firstOrFail();

        return redirect(URL::signedRoute('manage.show', ['portfolio' => $portfolio]));
    })->name('local.simple-fixture');
}
Route::post('/store', [PortfolioController::class, 'store'])->middleware('throttle:20,1')->name('portfolios.store');

Route::get('/portfolio/{portfolio:slug}/select-template', [PortfolioController::class, 'selectTemplate'])
    ->middleware('portfolio.owner')
    ->name('portfolio.select-template');
Route::post('/portfolio/{portfolio:slug}/save-template', [PortfolioController::class, 'saveTemplate'])
    ->middleware(['portfolio.owner', 'throttle:20,1'])
    ->name('portfolio.save-template');
Route::get('/p/{portfolio:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/p/{portfolio:slug}/img/{image}', [PortfolioImageController::class, 'show'])->name('portfolio.image');

Route::middleware('portfolio.owner')->group(function () {
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
