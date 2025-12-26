<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    public function basic()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function detailed()
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    private function checkApp(): array
    {
        return [
            'status' => 'ok',
            'environment' => config('app.env'),
            'debug' => config('app.debug') ? 'enabled' : 'disabled',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $tables = [
                'users' => DB::table('users')->count(),
                'roles' => DB::table('roles')->count(),
                'affiliates' => DB::table('affiliates')->count(),
                'personal_access_tokens' => DB::table('personal_access_tokens')->count(),
            ];
            
            return [
                'status' => 'ok',
                'connection' => 'connected',
                'tables' => $tables,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database check failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            
            return [
                'status' => $value === 'test' ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache check failed',
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $path = storage_path('app');
            $writable = is_writable($path);
            
            return [
                'status' => $writable ? 'ok' : 'error',
                'writable' => $writable,
                'disk' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
            ];
        }
    }
}
