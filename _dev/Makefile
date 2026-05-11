.PHONY: help up down restart logs shell install install-module fix-permissions test clean ps-install switch switch-1.7.8 switch-8.1 switch-9 switch-nightly switch-9.1.1-php83 switch-9.1.1-php84 switch-9.1.1-php85 multi multi-down

# Load .env file
include .env
export

# Project name: PS version + PHP version (if set) for full isolation
COMPOSE_PROJECT := ps$(subst .,,$(PS_VERSION))$(if $(PHP_VERSION),php$(subst .,,$(PHP_VERSION)),)

# Full image tag: PS_VERSION with optional -PHP_VERSION suffix
PS_IMAGE_TAG := $(PS_VERSION)$(if $(PHP_VERSION),-$(PHP_VERSION),)

help: ## Display help
	@grep -E '^[a-zA-Z0-9._-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start the PrestaShop environment
	@echo "Starting PrestaShop $(PS_IMAGE_TAG)..."
	docker compose -p $(COMPOSE_PROJECT) up -d
	@echo "PrestaShop $(PS_IMAGE_TAG) available at http://localhost:${PS_PORT}"
	@echo "phpMyAdmin available at http://localhost:${PMA_PORT}"

down: ## Stop the environment
	@echo "Stopping PrestaShop $(PS_IMAGE_TAG)..."
	docker compose -p $(COMPOSE_PROJECT) down

restart: down up ## Restart the environment

logs: ## Show logs
	docker compose -p $(COMPOSE_PROJECT) logs -f prestashop

shell: ## Access the PrestaShop container shell
	docker compose -p $(COMPOSE_PROJECT) exec prestashop bash

install: ## Install composer dependencies
	composer install

fix-permissions: ## Fix cache/log directory permissions (run if you get storage directory errors)
	docker compose -p $(COMPOSE_PROJECT) exec prestashop chown -R www-data:www-data /var/www/html/var

install-module: ## Install the module in PrestaShop
	docker compose -p $(COMPOSE_PROJECT) exec prestashop bin/console prestashop:module install hhmodulesmanager

test: ## Run PHPUnit tests
	php vendor/bin/phpunit -c tests/phpunit.xml.dist

clean: ## Completely remove the environment (deletes data)
	@echo "Removing PrestaShop $(PS_IMAGE_TAG) and its data..."
	docker compose -p $(COMPOSE_PROJECT) down -v

ps-install: up ## Start installation and open browser
	@echo "Installing PrestaShop $(PS_IMAGE_TAG)"
	@echo "Access http://localhost:${PS_PORT}"
	@sleep 5
	@command -v open >/dev/null && open http://localhost:${PS_PORT} || echo "Open http://localhost:${PS_PORT} in your browser"

# Version switching commands
switch-1.7.8: ## Switch to PrestaShop 1.7.8
	@$(MAKE) switch PS_VERSION=1.7.8 PHP_VERSION= PS_PORT=8090 MYSQL_PORT=3310 PMA_PORT=8091

switch-8.1: ## Switch to PrestaShop 8.1
	@$(MAKE) switch PS_VERSION=8.1 PHP_VERSION= PS_PORT=8092 MYSQL_PORT=3311 PMA_PORT=8093

switch-9: ## Switch to PrestaShop 9
	@$(MAKE) switch PS_VERSION=9 PHP_VERSION= PS_PORT=8094 MYSQL_PORT=3312 PMA_PORT=8095

switch-nightly: ## Switch to PrestaShop nightly
	@$(MAKE) switch PS_VERSION=nightly PHP_VERSION= PS_PORT=8096 MYSQL_PORT=3313 PMA_PORT=8097

switch-9.1.1-php83: ## Switch to PrestaShop 9.1.1 with PHP 8.3
	@$(MAKE) switch PS_VERSION=9.1.1 PHP_VERSION=8.3 PS_PORT=8094 MYSQL_PORT=3312 PMA_PORT=8095

switch-9.1.1-php84: ## Switch to PrestaShop 9.1.1 with PHP 8.4
	@$(MAKE) switch PS_VERSION=9.1.1 PHP_VERSION=8.4 PS_PORT=8094 MYSQL_PORT=3312 PMA_PORT=8095

switch-9.1.1-php85: ## Switch to PrestaShop 9.1.1 with PHP 8.5
	@$(MAKE) switch PS_VERSION=9.1.1 PHP_VERSION=8.5 PS_PORT=8094 MYSQL_PORT=3312 PMA_PORT=8095

switch: ## Change PrestaShop version (internal use)
	@sed -i \
		-e "s/^PS_VERSION=.*/PS_VERSION=${PS_VERSION}/" \
		-e "s/^PS_PORT=.*/PS_PORT=${PS_PORT}/" \
		-e "s/^MYSQL_PORT=.*/MYSQL_PORT=${MYSQL_PORT}/" \
		-e "s/^PMA_PORT=.*/PMA_PORT=${PMA_PORT}/" \
		.env
	@grep -q '^PHP_VERSION=' .env && \
		sed -i "s/^PHP_VERSION=.*/PHP_VERSION=$(PHP_VERSION)/" .env || \
		echo "PHP_VERSION=$(PHP_VERSION)" >> .env
	@grep -q '^PS_IMAGE_TAG=' .env && \
		sed -i "s/^PS_IMAGE_TAG=.*/PS_IMAGE_TAG=$(PS_IMAGE_TAG)/" .env || \
		echo "PS_IMAGE_TAG=$(PS_IMAGE_TAG)" >> .env
	@grep -q '^COMPOSE_PROJECT_NAME=' .env && \
		sed -i "s/^COMPOSE_PROJECT_NAME=.*/COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT)/" .env || \
		echo "COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT)" >> .env
	@echo "Switched to PrestaShop $(PS_IMAGE_TAG)"
	@echo "Ports: PrestaShop=${PS_PORT}, MySQL=${MYSQL_PORT}, phpMyAdmin=${PMA_PORT}"
	@echo "Run 'make up' to start"

# Run multiple versions simultaneously
multi: ## Start all versions in parallel
	@echo "Starting multiple PrestaShop versions..."
	PS_VERSION=1.7.8 PS_IMAGE_TAG=1.7.8 COMPOSE_PROJECT_NAME=ps178 PS_PORT=8090 MYSQL_PORT=3310 PMA_PORT=8091 docker compose -p ps178 up -d
	PS_VERSION=8.1 PS_IMAGE_TAG=8.1 COMPOSE_PROJECT_NAME=ps81 PS_PORT=8092 MYSQL_PORT=3311 PMA_PORT=8093 docker compose -p ps81 up -d
	PS_VERSION=9 PS_IMAGE_TAG=9 COMPOSE_PROJECT_NAME=ps9 PS_PORT=8094 MYSQL_PORT=3312 PMA_PORT=8095 docker compose -p ps9 up -d
	PS_VERSION=nightly PS_IMAGE_TAG=nightly COMPOSE_PROJECT_NAME=psnightly PS_PORT=8096 MYSQL_PORT=3313 PMA_PORT=8097 docker compose -p psnightly up -d
	@echo "Available versions:"
	@echo "   - PrestaShop 1.7.8:   http://localhost:8090"
	@echo "   - PrestaShop 8.1:     http://localhost:8092"
	@echo "   - PrestaShop 9:       http://localhost:8094"
	@echo "   - PrestaShop nightly: http://localhost:8096"

multi-down: ## Stop all versions
	docker compose -p ps178 down
	docker compose -p ps81 down
	docker compose -p ps9 down
	docker compose -p psnightly down
