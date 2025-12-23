#!/bin/bash

echo "🔍 Verifying Production Setup..."

errors=0
warnings=0

# Check files
files=(
    ".env.production"
    "backup.sh"
    "deploy-production.sh"
    "nginx.conf"
    "marketplace-worker.service"
    "FINAL_DEPLOYMENT_CHECKLIST.md"
    "PRODUCTION_DEPLOYMENT_GUIDE.md"
)

echo "📁 Checking files..."
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file"
    else
        echo "  ✗ $file MISSING"
        ((errors++))
    fi
done

# Check directories
dirs=(
    "app/Http/Middleware"
    "app/Console/Commands"
    "tests/Feature"
    ".github/workflows"
)

echo ""
echo "📂 Checking directories..."
for dir in "${dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "  ✓ $dir"
    else
        echo "  ✗ $dir MISSING"
        ((errors++))
    fi
done

# Check middleware
echo ""
echo "🛡️ Checking middleware..."
if [ -f "app/Http/Middleware/SanitizeInput.php" ]; then
    echo "  ✓ SanitizeInput.php"
else
    echo "  ✗ SanitizeInput.php MISSING"
    ((errors++))
fi

if [ -f "app/Http/Middleware/SecurityHeaders.php" ]; then
    echo "  ✓ SecurityHeaders.php"
else
    echo "  ✗ SecurityHeaders.php MISSING"
    ((errors++))
fi

# Check tests
echo ""
echo "🧪 Checking tests..."
if [ -f "tests/Feature/AuthTest.php" ]; then
    echo "  ✓ AuthTest.php"
else
    echo "  ✗ AuthTest.php MISSING"
    ((errors++))
fi

if [ -f "tests/Feature/SecurityTest.php" ]; then
    echo "  ✓ SecurityTest.php"
else
    echo "  ✗ SecurityTest.php MISSING"
    ((errors++))
fi

# Summary
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ $errors -eq 0 ]; then
    echo "✅ All production files verified!"
    echo "📋 Next: Follow FINAL_DEPLOYMENT_CHECKLIST.md"
    exit 0
else
    echo "❌ Found $errors missing files"
    echo "⚠️  Please check the installation"
    exit 1
fi
