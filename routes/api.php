<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::prefix('v1')
    ->as('api.v1.')
    ->group(function (): void {
        Route::middleware('throttle:auth')->group(function (): void {
            Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
            Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
        });

        Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
            Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

            Route::post('posts', [PostController::class, 'store'])->name('posts.store');
            Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
            Route::patch('posts/{post}', [PostController::class, 'update'])->name('posts.patch');
            Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        });

        Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');

        Route::fallback(function () {
            return response()->json([
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'errors' => [
                    ['code' => 'not_found', 'detail' => 'Requested endpoint does not exist.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        });
    });
