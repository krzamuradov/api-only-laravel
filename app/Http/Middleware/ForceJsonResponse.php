<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
            return $response;
        }

        if (! $response instanceof JsonResponse) {
            return response()->json([
                'message' => Response::$statusTexts[$response->getStatusCode()] ?? 'Response',
            ], $response->getStatusCode());
        }

        return $response;
    }
}
