FROM weinstein/composer:latest as php-builder
COPY composer* ./
RUN php -f composer.phar install \
    --no-autoloader \
    --no-scripts \
    --no-interaction
COPY . .
RUN php -f composer.phar install --no-dev -o

FROM node:9.5.0 as js-builder
WORKDIR /app
COPY package*.json /app/
RUN npm install
COPY . .
RUN npm run build

FROM weinstein/webserver:latest
COPY . /var/www
COPY --from=php-builder /var/www/public/vendor /var/www/vendor
COPY --from=js-builder /app/public/js /var/www/public/js
USER root
RUN chown -R www-data /var/www && \
    chgrp -R 0 /var/www && \
    chmod -R g+rw /var/www/storage && \
    chmod -R u+rw /var/www/storage
WORKDIR /var/www
