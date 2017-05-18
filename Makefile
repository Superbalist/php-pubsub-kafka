.PHONY: tests

up:
	@./make-up.sh

down:
	@docker-compose stop -t 1

tests:
	@./vendor/bin/phpunit --configuration phpunit.xml

shell:
	@./make-shell.sh
