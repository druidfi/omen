test: PHP := 8.5
test: vendor
	@echo "Run composer test (all tests)"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-$(PHP) composer test

test-%: PHP := 8.5
test-%: vendor
	@echo "Run composer test-$*"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-$(PHP) composer test-$*

test-upsun: PHP := 8.5
test-upsun: vendor
	@echo "Run composer test-upsun"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:php-$(PHP) composer test-upsun

vendor:
	composer install
