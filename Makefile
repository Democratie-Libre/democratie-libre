configure:
	docker-compose run --rm --user root php chmod -R 777 ./var

install:
	make configure
	docker-compose run --rm php composer install
	docker-compose run --rm php ./bin/console doctrine:database:create --if-not-exists
	docker-compose run --rm php ./bin/console doctrine:schema:create
	docker-compose run --rm php ./bin/console rad:fixtures:load

up:
	docker-compose up -d

stan:
	docker-compose run --rm php composer stan

specs:
	docker-compose run --rm php composer specs
