<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    public function recordMetric(string $name, float $value, array $tags = []): void
    {
        $metric = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('performance')->info('Metric recorded', $metric);
    }

    public function trackQueryPerformance(): void
    {
        DB::listen(function ($query) {
            if ($query->time > 1000) { // Slow query threshold: 1 second
                Log::channel('performance')->warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                ]);
            }
        });
    }

    public function getSystemMetrics(): array
    {
        return [
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
            ],
            'database' => [
                'connections' => $this->getDatabaseConnections(),
            ],
            'cache' => [
                'hit_rate' => $this->getCacheHitRate(),
            ],
            'queue' => [
                'pending_jobs' => $this->getPendingJobs(),
                'failed_jobs' => $this->getFailedJobs(),
            ],
        ];
    }

    private function getDatabaseConnections(): int
    {
        try {
            $result = DB::select("SELECT count(*) as count FROM pg_stat_activity WHERE datname = ?", [config('database.connections.pgsql.database')]);
            return $result[0]->count ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getCacheHitRate(): float
    {
        try {
            $hits = Cache::get('cache_hits', 0);
            $misses = Cache::get('cache_misses', 0);
            $total = $hits + $misses;
            
            return $total > 0 ? ($hits / $total) * 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPendingJobs(): int
    {
        try {
            return DB::table('jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getFailedJobs(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
