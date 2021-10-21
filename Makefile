test: vendor
	@echo "Run composer test (all tests)"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-7.4 composer test

test-%: vendor
	@echo "Run composer test-$*"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-7.4 composer test-$*

vendor:
	composer install
