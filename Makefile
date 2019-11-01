test:
	@echo run tests
	@docker run --rm -it --volume $$PWD:/app druidfi/drupal:7.3 composer test
