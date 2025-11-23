@echo off
echo Clearing Laravel cache...
cd /d "%~dp0"
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo Cache cleared successfully!

