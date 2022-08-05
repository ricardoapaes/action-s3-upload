FROM likesistemas/php-dev:80 as build
WORKDIR /var/www/public/
COPY composer.json composer.lock ./
ARG GITHUB_TOKEN
RUN composer-config
RUN composer install --no-dev --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload

FROM php:8.0-cli

RUN apt-get update && apt-get install -y \
    curl 
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /application/
COPY --from=build /var/www/public/vendor/ ./vendor/
COPY --from=build /var/www/public/src/ ./src/
COPY cli .
RUN chmod +x cli
RUN php cli

ENTRYPOINT [ "php", "/application/cli" ]