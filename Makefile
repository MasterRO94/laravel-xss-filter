.PHONY: test

install:
	composer update

test:
	./vendor/bin/pest
