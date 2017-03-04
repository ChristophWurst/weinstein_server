all: build

build:
	./node_modules/grunt-cli/bin/grunt uglify

install-deps:
	npm install
	./node_modules/bower/bin/bower install
	composer install

clean:
	rm -rf node_modules
	rm -rf public/js/vendor
	rm -rf vendor
