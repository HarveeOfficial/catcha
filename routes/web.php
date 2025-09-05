<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CatchController;
use App\Http\Controllers\CatchAnalyticsController;
use App\Http\Controllers\GuidanceController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\WeatherForecastController;
use App\Http\Controllers\WeatherCityController;
use App\Http\Controllers\AiConsultController;
use App\Http\Controllers\AiConversationController;
use App\Http\Controllers\AiGuidanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth','verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Catches
    Route::get('/catches', [CatchController::class, 'index'])->name('catches.index');
    Route::get('/catches/create', [CatchController::class, 'create'])->name('catches.create');
    Route::post('/catches', [CatchController::class, 'store'])->name('catches.store');
    Route::get('/catches/analytics', CatchAnalyticsController::class)->name('catches.analytics');
    // Weather endpoint (AJAX)
    Route::get('/weather/current', WeatherController::class)->name('weather.current');
    Route::get('/weather/forecast', WeatherForecastController::class)->name('weather.forecast');
    Route::get('/weather/city', WeatherCityController::class)->name('weather.city');
    // Removed standalone weather page; weather now integrated into dashboard
    // AI consult endpoint
    Route::post('/ai/consult', AiConsultController::class)->name('ai.consult');
    Route::view('/ai/consult', 'ai.consult');
    Route::view('/ai/chat', 'ai.chat')->name('ai.chat');
    Route::get('/ai/conversations', [AiConversationController::class,'index'])->name('ai.conversations.index');
    Route::get('/ai/conversations/{conversation}', [AiConversationController::class,'show'])->name('ai.conversations.show');
    Route::delete('/ai/conversations/{conversation}', [AiConversationController::class,'destroy'])->name('ai.conversations.destroy');
    Route::post('/ai/messages/{message}/to-guidance', [AiGuidanceController::class,'store'])->name('ai.messages.to-guidance');
    // Feedback (experts/admins can submit; all authenticated can view list)
    Route::get('/catches/{fishCatch}/feedback', [\App\Http\Controllers\CatchFeedbackController::class,'index'])->name('catches.feedback.index');
    Route::post('/catches/{fishCatch}/feedback', [\App\Http\Controllers\CatchFeedbackController::class,'store'])->name('catches.feedback.store');
    Route::delete('/feedback/{feedback}', [\App\Http\Controllers\CatchFeedbackController::class,'destroy'])->name('catches.feedback.destroy');
    Route::post('/feedback/{feedback}/like', [\App\Http\Controllers\CatchFeedbackController::class,'like'])->name('catches.feedback.like');
    Route::delete('/feedback/{feedback}/like', [\App\Http\Controllers\CatchFeedbackController::class,'unlike'])->name('catches.feedback.unlike');

    // Guidance (restrict creation to experts/admin later)
    Route::get('/guidances', [GuidanceController::class, 'index'])->name('guidances.index');
    Route::get('/guidances/create', [GuidanceController::class, 'create'])->name('guidances.create');
    Route::post('/guidances', [GuidanceController::class, 'store'])->name('guidances.store');
    Route::get('/guidances/{guidance}', [GuidanceController::class,'show'])->name('guidances.show');
    Route::get('/guidances/{guidance}/edit', [GuidanceController::class,'edit'])->name('guidances.edit');
    Route::patch('/guidances/{guidance}', [GuidanceController::class,'update'])->name('guidances.update');
    Route::delete('/guidances/{guidance}', [GuidanceController::class,'destroy'])->name('guidances.destroy');
    Route::post('/guidances/{guidance}/approve', [GuidanceController::class,'approve'])->name('guidances.approve');
    Route::post('/guidances/{guidance}/reject', [GuidanceController::class,'reject'])->name('guidances.reject');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
