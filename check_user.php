<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('email', 'admin@marketplace.com')->first();

if (!$user) {
    echo "USER_NOT_FOUND\n";
    exit;
}

echo "EMAIL: " . $user->email . "\n";
echo "VERIFIED_AT: " . ($user->email_verified_at ?? 'NULL') . "\n";
echo "ROLES: " . implode(',', $user->getRoleNames()->toArray()) . "\n";

if ($user->hasRole('admin')) {
    echo "HAS_ADMIN_ROLE: YES\n";
} else {
    echo "HAS_ADMIN_ROLE: NO\n";
}
