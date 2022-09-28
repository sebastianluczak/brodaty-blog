# Executables
PHP      = php
COMPOSER = composer
SYMFONY  = bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— PHP 💻 ———————————————————————————————————————————————————————————————
phpunit: ## Fires PHP Unit tests
	@$(eval c=bin/phpunit)
	@$(PHP) $(c)

cs-fix: ## Runs PHP-CS-Fixer against ./src
	@$(eval c=vendor/bin/php-cs-fixer fix src)
	@$(PHP) $(c)

phpstan: ## Fires PHPStan
	@$(eval c=vendor/bin/phpstan analyse src -l max)
	@$(PHP) $(c)