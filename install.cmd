@echo off
composer update
composer install
npm install
npm run build
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
echo "Pensez bien à faire exécuter le script triggers.sql par mysql"
pause