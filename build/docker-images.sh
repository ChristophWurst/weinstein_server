#!/bin/bash

set -ev

if [ "$TRAVIS_BRANCH" != "master" ] || [ $TRAVIS_PHP_VERSION != "7.1" ]; then 
	exit 0;
fi

docker build -t weinstein/app -f deploy/app.docker .
docker build -t weinstein/web -f deploy/web.docker .

docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
docker push weinstein/app
docker push weinstein/web

