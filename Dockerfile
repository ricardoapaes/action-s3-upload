FROM likesistemas/php-dev:80 as build
WORKDIR /var/www/public/
COPY composer.json composer.lock ./
ARG GITHUB_TOKEN
RUN composer-config
RUN composer install --no-dev --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload

FROM build as phar
RUN curl -JOL https://clue.engineering/phar-composer-latest.phar
RUN php -d phar.readonly=off phar-composer-*.phar build
RUN chmod +x action-s3.phar
RUN php action-s3.phar

FROM php:8.0-cli

RUN apt-get update && apt-get install -y \
    curl 
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /application/
COPY --from=phar /var/www/public/action-s3.phar action-s3
RUN chmod +x action-s3 && php action-s3

ENTRYPOINT [ "php", "/application/action-s3" ]