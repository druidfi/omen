test:
	@echo run tests
	@docker run --rm -it --volume $$PWD:/app druidfi/php:7.3-fpm composer test
