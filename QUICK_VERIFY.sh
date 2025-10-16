#!/bin/bash
#
# Quick Self-Containment Verification Script
# Run this to verify everything is in the project folder
#

echo "🔍 SMS Management System - Self-Containment Check"
echo "================================================="
echo ""

PROJECT_DIR="/Users/mooseman/Desktop/www/sms-management-system"

# Check we're in the right place
if [ ! -f "$PROJECT_DIR/artisan" ]; then
    echo "❌ Not in project directory!"
    echo "Expected: $PROJECT_DIR"
    exit 1
fi

cd "$PROJECT_DIR"

echo "✅ Project Directory: $PROJECT_DIR"
echo ""

# Check database location
echo "📊 Database Files:"
find . -name "*.sqlite" -type f | while read file; do
    size=$(du -h "$file" | cut -f1)
    echo "  ✅ $file ($size)"
done
echo ""

# Check .env file
echo "🔐 Environment Configuration:"
if [ -f ".env" ]; then
    echo "  ✅ .env file exists (gitignored)"
else
    echo "  ⚠️  .env file missing (run: cp .env.example .env)"
fi

if [ -f ".env.example" ]; then
    echo "  ✅ .env.example template exists"
fi
echo ""

# Check .gitignore
echo "🚫 Git Ignore Status:"
if git check-ignore .env > /dev/null 2>&1; then
    echo "  ✅ .env is gitignored"
else
    echo "  ⚠️  .env might not be gitignored!"
fi

if git check-ignore database/database.sqlite > /dev/null 2>&1; then
    echo "  ✅ SQLite database is gitignored"
else
    echo "  ⚠️  Database not explicitly gitignored (check .gitignore)"
fi

if git check-ignore storage/logs/laravel.log > /dev/null 2>&1; then
    echo "  ✅ Log files are gitignored"
else
    echo "  ⚠️  Logs might not be gitignored!"
fi
echo ""

# Check storage directories
echo "💾 Storage Structure:"
for dir in storage/app storage/framework storage/logs; do
    if [ -d "$dir" ]; then
        echo "  ✅ $dir exists"
    else
        echo "  ❌ $dir missing!"
    fi
done
echo ""

# Check for external paths in code
echo "🔍 Checking for external path references..."
external_paths=0

# Check for /tmp references
if grep -r "/tmp" app/ config/ 2>/dev/null | grep -v ".git" | grep -q .; then
    echo "  ⚠️  Found /tmp references (might be OK if intentional)"
    external_paths=$((external_paths + 1))
fi

# Check for /var references
if grep -r "/var" app/ config/ 2>/dev/null | grep -v ".git" | grep -q .; then
    echo "  ⚠️  Found /var references (might be OK if intentional)"
    external_paths=$((external_paths + 1))
fi

# Check for absolute paths that aren't in project
if grep -r "\/Users\/" app/ config/ 2>/dev/null | grep -v ".git" | grep -v "$PROJECT_DIR" | grep -q .; then
    echo "  ⚠️  Found absolute paths outside project"
    external_paths=$((external_paths + 1))
fi

if [ $external_paths -eq 0 ]; then
    echo "  ✅ No unexpected external paths found"
fi
echo ""

# Check dependencies
echo "📦 Dependencies:"
if [ -d "vendor" ]; then
    echo "  ✅ PHP dependencies installed (vendor/)"
else
    echo "  ⚠️  PHP dependencies not installed (run: composer install)"
fi

if [ -d "node_modules" ]; then
    echo "  ✅ Node dependencies installed (node_modules/)"
else
    echo "  ⚠️  Node dependencies not installed (run: npm install)"
fi
echo ""

# Summary
echo "================================================="
echo "Summary:"
echo ""
echo "Project is self-contained if:"
echo "  ✅ All data files are in project directory"
echo "  ✅ .env file is gitignored"
echo "  ✅ No hardcoded external paths"
echo "  ✅ Storage directories exist and are writable"
echo ""
echo "To deploy, you only need to copy this entire folder"
echo "and run 'composer install' on the production server."
echo ""
echo "================================================="

