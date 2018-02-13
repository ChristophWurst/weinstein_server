FROM node:9.5.0 as js-builder
WORKDIR /app
COPY package*.json /app/
RUN npm install
COPY . .
RUN ./node_modules/bower/bin/bower install --allow-root
RUN ./node_modules/grunt-cli/bin/grunt

FROM composer:latest as php-builder
COPY . /app
RUN composer install --ignore-platform-reqs --no-dev -o

FROM weinstein/webserver:latest
COPY . /var/www
COPY --from=js-builder /app/public/css/*.css /var/www/public/css/
COPY --from=js-builder /app/public/js /var/www/public/js
COPY --from=php-builder /app/vendor /var/www/vendor
USER root
RUN chown -R www-data /var/www && \
    chgrp -R 0 /var/www && \
    chmod -R g+rw /var/www/storage && \
    chmod -R u+rw /var/www/storage
USER www-data
WORKDIR /var/www
