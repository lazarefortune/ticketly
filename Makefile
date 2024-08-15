.PHONY: install deploy

deploy: vendor/autoload.php
	ssh lazarefortune 'cd app && git pull origin main && make install'

install: vendor/autoload.php
	npm install
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate --no-interaction

vendor/autoload.php: composer.lock composer.json
	composer install --optimize-autoloader