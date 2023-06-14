FROM php:7.4-cli

# Обновляем
RUN apt-get update && \
    apt-get install -y --no-install-recommends

RUN apt-get install -y netcat

# Установка расширения PDO_Mysql
RUN docker-php-ext-install pdo pdo_mysql

# Установка Composer
#RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
#    && rm -rf /root/.composer/cache

CMD ["php", "/app/script.php"]

