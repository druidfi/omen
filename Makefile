test:
	@echo run tests
	@docker run --rm -it --hostname local.drupal.com --volume $$PWD:/app druidfi/drupal:7.3 composer test
