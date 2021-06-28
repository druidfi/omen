test:
	@echo "Run composer test (all tests)"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:7.4 composer test

test-%:
	@echo "Run composer test-$*"
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:7.4 composer test-$*
