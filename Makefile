all: build

build:
	./node_modules/grunt-cli/bin/grunt uglify

install-deps:
	yarn install
	./node_modules/bower/bin/bower install
	composer install

test:
	./vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml

clean:
	rm -rf node_modules
	rm -rf public/js/vendor
	rm -rf vendor
