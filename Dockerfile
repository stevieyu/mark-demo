FROM alpine AS base

ENV APP_PATH=/app

WORKDIR $APP_PATH

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories && \
    apk add --no-cache php8 php8-iconv php8-ctype php8-simplexml php8-curl php8-fileinfo php8-openssl php8-opcache \
    php8-posix php8-pcntl \
    php8-pecl-imagick && \
    ln -s /usr/bin/php8 /usr/bin/php



FROM base AS install

ADD https://install.phpcomposer.com/composer.phar /usr/bin/composer
RUN apk add --no-cache php8-phar php8-mbstring && \
    chmod a+x /usr/bin/composer && \
    composer config -g repositories.packagist composer https://mirrors.aliyun.com/composer/ && \
    composer config -g secure-http false
COPY composer.json .
COPY composer.lock .
RUN composer install --no-dev --prefer-dist



FROM base AS runner

COPY --from=install $APP_PATH/vendor ./vendor
COPY . .

RUN echo "ini" && \
    echo "opcache.enable=1" >> /etc/php8/conf.d/00_opcache.ini && \
    echo "opcache.enable_cli=1" >> /etc/php8/conf.d/00_opcache.ini  && \
    echo "opcache.file_cache=/tmp" >> /etc/php8/conf.d/00_opcache.ini && \
    echo "opcache.jit_buffer_size=64M" >> /etc/php8/conf.d/00_opcache.ini && \
    echo "end ini"

CMD ["$APP_PATH/bootstrap"]
