# Выполним по умолчанию, при запуске пустого make
.DEFAULT_GOAL := help

# Считываем файл, всё что содержит двойную решётку # Это описание к командам
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-17s\033[0m %s\n", $$1, $$2}'
.PHONY: help

# Подключим файл конфигурации
include .env

delay: # Задержка, она требуется, чтоб MYSQL успел инициализироваться
	@sleep 30
.PHONY: delay

# В зависимости от окружения, мы подключаем разные профили
ifeq ($(ENVIRONMENT),production)
	PROFILE :=
else
	PROFILE := --profile debug
endif

# Добавим красоты и чтоб наши команды было видно в теле скрипта
PURPLE = \033[1;35m--
RESET = --\033[0m

init: ## Инициализация проекта
init: docker-down docker-pull docker-build docker-up composer-install delay import-db migrate

update: ## Пересобрать контейнер, обновить композер и миграции
update: docker-down docker-pull docker-build docker-up composer-install migrate

restart: ## Restart docker containers
restart: docker-down docker-up

down: ## Остановить все контейнеры
	@echo "${PURPLE} Остановим все контейнеры ${RESET}"
	@docker-down

php-bash: ## Подключается к контейнеру PHP
	docker-compose exec php-fpm bash

migrate: ## Применить миграции
	@echo "${PURPLE} Применить миграции ${RESET}"
	docker-compose run --rm php-cli php app/vendor/bin/phinx migrate --configuration app/phinx.php

import-db: ## Импорт образа бд
	@echo "${PURPLE} Импорт БД ${RESET}"
	@#gunzip < ./dump.sql.gz | docker-compose exec -T mysql mysql -uroot -p${MYSQL_ROOT_PASSWORD} BD

composer-install: ## Поставим пакеты композера
	@echo "${PURPLE} Поставим пакеты композера ${RESET}"
	@#docker-compose run --rm php-cli pwd ##composer install

docker-up: ## Поднимем контейнеры
	@echo "${PURPLE} Поднимем контейнеры ${RESET}"
	docker-compose $(PROFILE) up -d

docker-build: ## Соберём образы
	@echo "${PURPLE} Соберём образы ${RESET}"
	docker-compose $(PROFILE) build

docker-pull: ## Поучим все контейнеры
	@echo "${PURPLE} Поучим все контейнеры ${RESET}"
	docker-compose $(PROFILE) pull --include-deps

docker-down: ## Остановим контейнеры
	docker-compose $(PROFILE) down --remove-orphans

