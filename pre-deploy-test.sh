#!/bin/bash

echo "🧪 Running Pre-Deployment Tests..."
echo ""

# Run production validation
echo "1️⃣ Validating production configuration..."
php artisan production:validate
if [ $? -ne 0 ]; then
    echo "❌ Production validation failed"
    exit 1
fi
echo ""

# Run tests
echo "2️⃣ Running test suite..."
php artisan test
if [ $? -ne 0 ]; then
    echo "❌ Tests failed"
    exit 1
fi
echo ""

# Check composer dependencies
echo "3️⃣ Checking dependencies..."
composer validate --no-check-publish
if [ $? -ne 0 ]; then
    echo "⚠️  Composer validation warnings"
fi
echo ""

# Check for .env file
echo "4️⃣ Checking environment..."
if [ ! -f ".env" ]; then
    echo "❌ .env file not found"
    exit 1
fi
echo "✓ .env file exists"
echo ""

# Check database connection
echo "5️⃣ Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';"
if [ $? -ne 0 ]; then
    echo "❌ Database connection failed"
    exit 1
fi
echo ""

# Check storage permissions
echo "6️⃣ Checking permissions..."
if [ ! -w "storage" ]; then
    echo "❌ storage/ not writable"
    exit 1
fi
if [ ! -w "bootstrap/cache" ]; then
    echo "❌ bootstrap/cache/ not writable"
    exit 1
fi
echo "✓ Permissions OK"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ All pre-deployment checks passed!"
echo "🚀 Ready for deployment"
