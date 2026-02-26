<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->append(ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                'errors' => collect($e->errors())
                    ->flatMap(fn (array $messages, string $field) => collect($messages)->map(
                        fn (string $message): array => ['field' => $field, 'detail' => $message]
                    ))
                    ->values(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'errors' => [
                    ['code' => 'resource_not_found', 'detail' => 'Resource not found.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => Response::$statusTexts[Response::HTTP_FORBIDDEN],
                'errors' => [
                    ['code' => 'forbidden', 'detail' => $e->getMessage() ?: 'Forbidden.'],
                ],
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                'errors' => [
                    ['code' => 'unauthenticated', 'detail' => 'Authentication is required.'],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $e->getStatusCode();

            return response()->json([
                'message' => Response::$statusTexts[$status] ?? 'HTTP Error',
                'errors' => [
                    ['code' => 'http_error', 'detail' => $e->getMessage() ?: 'Request failed.'],
                ],
            ], $status);
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                'errors' => [
                    ['code' => 'server_error', 'detail' => 'Unexpected server error.'],
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
