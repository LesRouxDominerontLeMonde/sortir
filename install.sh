#!/usr/bin/env sh
composer update
composer install
npm install
npm run build
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:query:sql < type triggers.sql
php bin/console doctrine:fixtures:load