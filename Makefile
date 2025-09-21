.PHONY: install start stop restart logs clean

DOCKER_COMPOSE = docker compose

install: docker-down-clear \
	docker-pull docker-build docker-up \
	api-composer-install \
	api-permissions \
	api-npm-install \
	api-npm-build \
	api-database-prepare \
	api-fixtures-load

start:
	$(DOCKER_COMPOSE) up -d

stop:
	$(DOCKER_COMPOSE) down

restart: stop start

logs:
	$(DOCKER_COMPOSE) logs -f

clean: docker-down-clear

api-permissions:
	$(DOCKER_COMPOSE) exec app chmod -R 777 var

api-composer-install:
	$(DOCKER_COMPOSE) run --rm app composer install

api-npm-install:
	$(DOCKER_COMPOSE) run --rm app npm install

api-npm-build:
	$(DOCKER_COMPOSE) run --rm app npm run build

api-database-prepare:
	$(DOCKER_COMPOSE) exec app php bin/console doctrine:database:create --if-not-exists
	$(DOCKER_COMPOSE) exec app php bin/console doctrine:migrations:migrate -n

api-fixtures-load:
	$(DOCKER_COMPOSE) exec app php bin/console doctrine:fixtures:load -n

docker-down-clear:
	$(DOCKER_COMPOSE) down -v --remove-orphans

docker-pull:
	$(DOCKER_COMPOSE) pull

docker-build:
	$(DOCKER_COMPOSE) build

docker-up:
	$(DOCKER_COMPOSE) up -d