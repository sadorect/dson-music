<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class QueryMonitoring
{
    /**
     * Query count threshold - log if more than this many queries
     */
    const QUERY_COUNT_THRESHOLD = 50;

    /**
     * Execution time threshold in milliseconds - log if slower than this
     */
    const EXECUTION_TIME_THRESHOLD = 1000; // 1 second

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only monitor in non-production or when explicitly enabled
        if (! config('app.debug') && ! env('ENABLE_QUERY_MONITORING', false)) {
            return $next($request);
        }

        // Start monitoring
        $startTime = microtime(true);
        $queryCount = 0;
        $queries = [];

        // Listen to database queries
        DB::listen(function ($query) use (&$queryCount, &$queries) {
            $queryCount++;
            $queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
        });

        // Execute the request
        $response = $next($request);

        // Calculate execution time
        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        // Log if thresholds exceeded
        if ($queryCount > self::QUERY_COUNT_THRESHOLD) {
            Log::warning('High query count detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'query_count' => $queryCount,
                'execution_time_ms' => round($executionTime, 2),
                'threshold' => self::QUERY_COUNT_THRESHOLD,
                'user_id' => auth()->id(),
            ]);
        }

        if ($executionTime > self::EXECUTION_TIME_THRESHOLD) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => round($executionTime, 2),
                'query_count' => $queryCount,
                'threshold_ms' => self::EXECUTION_TIME_THRESHOLD,
                'user_id' => auth()->id(),
                'top_slow_queries' => $this->getTopSlowQueries($queries, 5),
            ]);
        }

        // Add debug headers in development
        if (config('app.debug')) {
            $response->headers->set('X-Query-Count', $queryCount);
            $response->headers->set('X-Execution-Time', round($executionTime, 2).'ms');
        }

        return $response;
    }

    /**
     * Get the top N slowest queries
     */
    protected function getTopSlowQueries(array $queries, int $limit = 5): array
    {
        // Sort by time descending
        usort($queries, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        // Return top N queries
        return array_slice(array_map(function ($query) {
            return [
                'sql' => $query['sql'],
                'time_ms' => round($query['time'], 2),
            ];
        }, $queries), 0, $limit);
    }
}
