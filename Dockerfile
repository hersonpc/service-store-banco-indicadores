FROM php:7.3-alpine

LABEL maintainer="Herson Melo <hersonpc@gmail.com>"

RUN docker-php-ext-install mysqli

COPY . /app
WORKDIR /app

EXPOSE 80

CMD [ "php", "-S", "0.0.0.0:80", "-t", "/app/public", "/app/public/index.php" ]