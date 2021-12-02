test: vendor
	@echo "Run composer test (all tests)"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-8.0 composer test

test-%: vendor
	@echo "Run composer test-$*"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-8.0 composer test-$*

vendor:
	composer install
