<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly Seller Payouts (e.g., every Monday)
Schedule::call(function () {
    // Logic to calculate and process seller payouts
    // This would typically involve aggregating 'pending' seller orders and creating Payout records
    \Illuminate\Support\Facades\Log::info('Processing weekly seller payouts...');
})->weeklyOn(1, '00:00');

// Monthly Affiliate Payouts (e.g., 1st of every month)
Schedule::call(function () {
    // Logic to process affiliate payouts
    \Illuminate\Support\Facades\Log::info('Processing monthly affiliate payouts...');
})->monthlyOn(1, '00:00');
