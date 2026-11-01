.PHONY: configure install up down test

configure:
	docker compose run --rm --user root php sh -c "mkdir -p var && chmod -R 777 var"

install: configure
	docker compose build
	docker compose run --rm php composer install
	docker compose run --rm php ./bin/console doctrine:database:create --if-not-exists
	docker compose run --rm php ./bin/console doctrine:schema:create

up:
	docker compose up -d

down:
	docker compose down

test:
	docker compose run --rm php sh -c "./bin/console doctrine:database:create --if-not-exists --env=test && ./bin/console doctrine:schema:update --force --env=test && bin/phpunit"

stan:
	docker compose run --rm php composer stan

specs:
	docker compose run --rm php composer specs
