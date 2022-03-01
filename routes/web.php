<?php

use Illuminate\Routing\Middleware\ValidateSignature;
use IronGate\Pkgtrends\Http\Controllers\TrendsController;
use IronGate\Pkgtrends\Http\Controllers\SubscriptionController;

Route::post('{query}/subscribe', [SubscriptionController::class, 'postSubscribe'])->where('query', '.*')->name('subscription.create');

Route::group([
    'middleware' => [ValidateSignature::class],
], function () {
    Route::get('subscription/{id}/confirm', [SubscriptionController::class, 'getConfirm'])->name('subscription.confirm');
    Route::post('subscription/{id}/confirm', [SubscriptionController::class, 'postConfirm']);
    Route::get('subscription/{id}/unsubscribe', [SubscriptionController::class, 'getUnsubscribe'])->name('subscription.unsubscribe');
    Route::post('subscription/{id}/unsubscribe', [SubscriptionController::class, 'postUnsubscribe']);
    Route::get('subscription/{email}/unsubscribe-all', [SubscriptionController::class, 'getUnsubscribeAll'])->name('subscription.unsubscribe_all');
    Route::post('subscription/{email}/unsubscribe-all', [SubscriptionController::class, 'postUnsubscribeAll']);
});

Route::get('search', [TrendsController::class, 'searchPackages']);
Route::get('{query?}', [TrendsController::class, 'showTrends'])->where('query', '.*')->name('home');
