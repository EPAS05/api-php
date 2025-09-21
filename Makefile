.PHONY: install start stop restart logs clean

install: docker-down-clear \
	api-permissions \
	docker-pull docker-build docker-up \
	api-composer-install \
	api-npm-install \
	api-npm-build \
	api-database-prepare \
	api-fixtures-load

start:
	docker-compose up -d

stop:
	docker-compose down

restart: stop start

logs:
	docker-compose logs -f

clean: docker-down-clear

api-permissions:
	docker-compose run --rm app chmod -R 777 var

api-composer-install:
	docker-compose run --rm app composer install

api-npm-install:
	docker-compose run --rm app npm install

api-npm-build:
	docker-compose run --rm app npm run build

api-database-prepare:
	docker-compose run --rm app bin/console doctrine:database:create --if-not-exists
	docker-compose run --rm app bin/console doctrine:migrations:migrate -n

api-fixtures-load:
	docker-compose run --rm app bin/console doctrine:fixtures:load -n

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

docker-up:
	docker-compose up -d