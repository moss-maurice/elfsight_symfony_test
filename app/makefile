PROJECT_NAME=elfsight_test
SYMFONY_VERSION=7.2.x
DIR_TMP=/tmp
DIR_WEB_ROOT=/var/www/html
DB_NAME=pgsql_db
DB_USER=pgsql_user
DB_PASSWORD=pgsql_pass
DB_HOST=postgresql
DB_PORT=5432
DB_VERSION=16
TTL=7776000

.PHONY: init create wipe setup install configur migrate fixture

init: wipe create install

install: setup configure migrate fixture

create:
	@echo " >>> INITIALIZE NEW PROJECT ..."
	@echo " >>>"
	@echo " >>> Installing Symfony"
	@rm -rf $(DIR_TMP)/symfony
	@mkdir -p $(DIR_TMP)/symfony
	@symfony new $(DIR_TMP)/symfony --version="$(SYMFONY_VERSION)" --webapp --no-interaction
	@echo " >>> Move Symfony in web-root"
	@rm -rf $(DIR_TMP)/symfony/.git
	@mv $(DIR_TMP)/symfony/* $(DIR_WEB_ROOT)
	@mv $(DIR_TMP)/symfony/.[!.]* $(DIR_WEB_ROOT)
	@echo " >>> Require advanced dependencies ..."
	@composer require lexik/jwt-authentication-bundle --no-interaction
	@composer require fakerphp/faker --no-interaction
	@composer require nesbot/carbon --no-interaction
	@composer require orm-fixtures --dev --no-interaction
	@composer require --dev symfony/test-pack --no-interaction
	@composer require --dev dama/doctrine-test-bundle --no-interaction
	@echo " >>> DONE!"
	@echo ""

wipe:
	@echo " >>> WIPE EXISTSING PROJECT ..."
	@echo " >>>"
	@echo " >>> Wipe all exclude makefile"
	@cp $(DIR_WEB_ROOT)/makefile $(DIR_TMP)/makefile
	@find $(DIR_WEB_ROOT)/ -mindepth 1 -delete
	@cp $(DIR_TMP)/makefile $(DIR_WEB_ROOT)/makefile
	@echo " >>> DONE!"
	@echo ""

setup:
	@echo " >>> INSTALL PROJECT ..."
	@echo " >>>"
	@echo " >>> Install dependencies ..."
	@composer install --no-interaction
	@composer require davmixcool/php-sentiment-analyzer --no-interaction
	@echo " >>> DONE!"
	@echo ""

configure:
	@echo " >>> CONFIGURE PROJECT ..."
	@echo " >>>"
	@echo " >>> Symfony configure"
	@echo "DATABASE_URL='pgsql://$(DB_USER):$(DB_PASSWORD)@$(DB_HOST):$(DB_PORT)/$(DB_NAME)?serverVersion=$(DB_VERSION)&charset=utf8'" > $(DIR_WEB_ROOT)/.env.local
	@echo "JWT_TOKEN_TTL=$(TTL)" >> $(DIR_WEB_ROOT)/.env.local
	@echo "FIXTURES_USERS_LIMIT=5" >> $(DIR_WEB_ROOT)/.env.local
	@echo "FIXTURES_COMMENTS_LIMIT=5" >> $(DIR_WEB_ROOT)/.env.local
	@cp $(DIR_WEB_ROOT)/.env.test $(DIR_TMP)/.env.test.local
	@echo "DATABASE_URL='pgsql://$(DB_USER):$(DB_PASSWORD)@$(DB_HOST):$(DB_PORT)/$(DB_NAME)?serverVersion=$(DB_VERSION)&charset=utf8'" >> $(DIR_WEB_ROOT)/.env.test.local
	@echo "JWT_TOKEN_TTL=$(TTL)" >> $(DIR_WEB_ROOT)/.env.test.local
	@echo "FIXTURES_USERS_LIMIT=5" >> $(DIR_WEB_ROOT)/.env.test.local
	@echo "FIXTURES_COMMENTS_LIMIT=5" >> $(DIR_WEB_ROOT)/.env.test.local
	@symfony console lexik:jwt:generate-keypair --overwrite --no-interaction
	@echo " >>> DONE!"
	@echo ""

migrate:
	@echo " >>> SYMFONY MIGRATING ..."
	@symfony console doctrine:database:create --if-not-exists --no-interaction
	@symfony console doctrine:migration:migrate --no-interaction
	@echo " >>> DONE!"
	@echo ""

fixture:
	@echo " >>> SYMFONY FIXTURING ..."
	@symfony console doctrine:fixtures:load --append --no-interaction
	@echo " >>> DONE!"
	@echo ""

test:
	@symfony console doctrine:database:create --env=test --if-not-exists --no-interaction
	@$(DIR_WEB_ROOT)/vendor/bin/phpunit
	@symfony console doctrine:database:drop --env=test --force --no-interaction
