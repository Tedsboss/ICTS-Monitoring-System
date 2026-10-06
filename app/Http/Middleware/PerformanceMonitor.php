<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $queries = DB::getQueryLog();

        $durationMs = round(
            (microtime(true) - $startTime) * 1000,
            2
        );

        $queryTimeMs = round(
            collect($queries)->sum(
                fn ($query) => (float) ($query['time'] ?? 0)
            ),
            2
        );

        $memoryMb = round(
            max(
                0,
                memory_get_usage(true) - $startMemory
            ) / 1024 / 1024,
            2
        );

        $queryCount = count($queries);

        $response->headers->set(
            'X-Performance-Duration-Ms',
            (string) $durationMs
        );

        $response->headers->set(
            'X-Performance-Query-Count',
            (string) $queryCount
        );

        $response->headers->set(
            'X-Performance-Query-Time-Ms',
            (string) $queryTimeMs
        );

        $response->headers->set(
            'X-Performance-Memory-Mb',
            (string) $memoryMb
        );

        Log::info('PERFORMANCE', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => optional($request->route())->getName(),
            'duration_ms' => $durationMs,
            'query_count' => $queryCount,
            'query_time_ms' => $queryTimeMs,
            'memory_mb' => $memoryMb,
        ]);

        DB::disableQueryLog();
        DB::flushQueryLog();

        return $response;
    }
}