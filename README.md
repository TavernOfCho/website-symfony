Symfony Cours
=============

Install
---------

```bash
docker-compose up -d
docker-compose exec php-fpm composer install
docker-compose exec php-fpm php bin/console doctrine:schema:update --force
docker-compose exec php-fpm php bin/console doctrine:fixtures:load
docker-compose exec php-fpm npm install
docker-compose exec php-fpm npm run dev
```