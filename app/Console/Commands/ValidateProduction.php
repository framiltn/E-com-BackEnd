<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateProduction extends Command
{
    protected $signature = 'production:validate';
    protected $description = 'Validate production environment configuration';

    public function handle()
    {
        $this->info('Validating production configuration...');
        $errors = [];
        $warnings = [];

        // Critical checks
        if (config('app.debug') === true) {
            $errors[] = 'APP_DEBUG must be false in production';
        }

        if (config('app.env') !== 'production') {
            $warnings[] = 'APP_ENV should be "production"';
        }

        if (empty(config('app.key'))) {
            $errors[] = 'APP_KEY is not set';
        }

        if (config('session.driver') === 'file') {
            $warnings[] = 'SESSION_DRIVER should be redis or database for production';
        }

        if (config('cache.default') === 'file') {
            $warnings[] = 'CACHE_STORE should be redis for production';
        }

        if (strpos(config('app.url'), 'localhost') !== false) {
            $warnings[] = 'APP_URL should be your production domain';
        }

        // Display results
        if (count($errors) > 0) {
            $this->error('Critical issues found:');
            foreach ($errors as $error) {
                $this->line("  ✗ {$error}");
            }
        }

        if (count($warnings) > 0) {
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->line("  ⚠ {$warning}");
            }
        }

        if (count($errors) === 0 && count($warnings) === 0) {
            $this->info('✓ All checks passed!');
            return 0;
        }

        return count($errors) > 0 ? 1 : 0;
    }
}
