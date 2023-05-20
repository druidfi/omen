test: PHP := 8.1
test: vendor
	@echo "Run composer test (all tests)"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-$(PHP) composer test

test-%: PHP := 8.1
test-%: vendor
	@echo "Run composer test-$*"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-$(PHP) composer test-$*

vendor:
	composer install
