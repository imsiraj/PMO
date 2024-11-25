cd /opt/edugem/apps/pmo-backend
sudo update-alternatives --set php /usr/bin/php8.1

git pull
composer install
php artisan migrate
php artisan config:clear
php artisan route:clear


