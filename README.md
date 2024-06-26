# REST APIs for Isurance System (PHP, MySQL, Laravel etc.)

## About

- [Docker](https://www.docker.com/) as the container service to isolate the environment.
- [Php](https://php.net/) to develop backend support.
- [Laravel](https://laravel.com) as the server framework / controller layer
- [MySQL](https://mysql.com/) as the database layer
- [NGINX](https://docs.nginx.com/nginx/admin-guide/content-cache/content-caching/) as a proxy / content-caching layer

## Starting the docker and test Cases

1. Clone git repo and share codebase directory with docker
2. You can run `docker-compose up` from terminal
3. Server can be accessed at `http://localhost:8888`
4. Run migration, create table:
	- Run migration: `docker exec customer_php php artisan migrate`
4. Run manual testcase suite:
	- Unit Tests: `docker exec customer_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Unit`
	- Integration Tests: `docker exec customer_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Feature`
	