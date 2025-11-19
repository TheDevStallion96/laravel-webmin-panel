<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\DeploymentController;

use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::apiResource('sites', SiteController::class);
    Route::apiResource('domains', DomainController::class);
    Route::apiResource('deployments', DeploymentController::class)->only(['index','show','store','update','destroy']);

    Route::get('/status', function (Request $request) {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            abort_unless($request->user()->tokenCan('view-site'), 403);
        }
        return response()->json(['status' => 'ok']);
    });

    Route::post('/manage-site-action', function (Request $request) {
        $bearer = $request->bearerToken();
        if ($bearer) {
            // Parse token ID and fetch token model directly to inspect abilities
            $tokenId = (string) strtok($bearer, '|');
            $patModel = \Laravel\Sanctum\PersonalAccessToken::query()->find($tokenId);
            $abilities = (array) ($patModel?->abilities ?? []);
            abort_unless(in_array('manage-site', $abilities, true), 403);
        } else {
            abort_unless($request->user()->isAdmin() || $request->user()->isDeveloper(), 403);
        }
        return response()->json(['done' => true]);
    });
});
