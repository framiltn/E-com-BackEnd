<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeDatabase extends Command
{
    protected $signature = 'db:optimize';
    protected $description = 'Optimize database tables and analyze query performance';

    public function handle()
    {
        $this->info('Starting database optimization...');

        DB::statement('ANALYZE');
        DB::statement('VACUUM ANALYZE');

        $this->info('Database optimization completed!');
        return 0;
    }
}
