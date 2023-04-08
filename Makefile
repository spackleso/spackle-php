default: install test

install:
	composer install
	composer dump-autoload

test:
	./vendor/bin/phpunit

