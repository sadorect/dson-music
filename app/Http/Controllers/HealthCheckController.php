<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    /**
     * Perform a health check on the application.
     */
    public function __invoke(): JsonResponse
    {
        $status = 'healthy';
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        // If any check fails, set status to unhealthy
        foreach ($checks as $check) {
            if (! $check['status']) {
                $status = 'unhealthy';
                break;
            }
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0'),
        ], $status === 'healthy' ? 200 : 503);
    }

    /**
     * Check database connection.
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'status' => true,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Database connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check cache system.
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.now()->timestamp;
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value === 'test') {
                return [
                    'status' => true,
                    'message' => 'Cache system operational',
                ];
            }

            return [
                'status' => false,
                'message' => 'Cache read/write verification failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Cache system error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check storage writability.
     */
    private function checkStorage(): array
    {
        try {
            $testFile = storage_path('framework/cache/health_check_'.now()->timestamp.'.tmp');
            $written = file_put_contents($testFile, 'test');

            if ($written !== false) {
                unlink($testFile);

                return [
                    'status' => true,
                    'message' => 'Storage is writable',
                ];
            }

            return [
                'status' => false,
                'message' => 'Storage write failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Storage check error: '.$e->getMessage(),
            ];
        }
    }
}
