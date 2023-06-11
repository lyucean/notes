.DEFAULT_GOAL := help
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-17s\033[0m %s\n", $$1, $$2}'
.PHONY: help

include .env

init: ## Инициализация проекта
init: docker-down docker-pull docker-build docker-up composer-install import-db-dump migrate

update: ## Пересобрать контейнер, обновить композер и миграции
update: docker-down docker-pull docker-build docker-up composer-install migrate

restart: ## Restart docker containers
restart: docker-down docker-up

php-bash: ## Подключается к контейнеру PHP
	docker-compose exec php-fpm bash

migrate: ## Применить миграции
	echo 'Применить миграции'
	#docker-compose run --rm php-fpm php vendor/bin/phinx migrate

import-db-dump: ## Имортируем образ бд
	echo 'Импорт БД'
	#gunzip < ./dump.sql.gz | docker-compose exec -T mysql mysql -uroot -p${MYSQL_ROOT_PASSWORD} BD

composer-install: ## поставим пакеты композера
	docker-compose run --rm php-fpm composer install

docker-up: ## Поднимем контейнеры в фоне
	docker-compose up -d

docker-build: ## соберём образы
	docker-compose build

docker-pull: ## поучим все контейнеры
	docker-compose pull --include-deps

docker-down: ## Остановим контенры
	docker-compose down --remove-orphans

