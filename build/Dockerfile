ARG PHP_VERSION

FROM php:$PHP_VERSION-cli-alpine

ARG BUILD_TAG
ARG MSODBC_SQL_APK_URI
ARG MSSQL_TOOLS_APK_URI
ARG MSODBC_SQL_SIG_URI
ARG MSSQL_TOOLS_SIG_URI

ENV BUILD_TAG=$BUILD_TAG
ENV COMPOSER_HOME=/tmp/composer
ENV COMPOSER_NO_INTERACTION=1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./pseudify /opt/pseudify/pseudify
COPY ./entrypoint /usr/local/bin/entrypoint

LABEL maintainer="waldhacker <hello@waldhacker.dev>"

RUN \
    apk -U update \
    && apk add --no-cache \
        autoconf \
        bash \
        dpkg-dev \
        dpkg \
        file \
        g++ \
        gcc \
        git \
        gnupg \
        libc-dev \
        make \
        mariadb-client \
        mysql-client \
        pkgconf \
        postgresql-client \
        postgresql-dev \
        re2c \
        rsync \
        runit \
        shadow \
        sqlite \
        sudo \
        unixodbc-dev \
    \
    && update-ca-certificates \
    \
    \
    && addgroup -g 61042 pseudify \
    && adduser --uid 61042 --shell /bin/bash --home /home/pseudify/ --disabled-password --ingroup pseudify pseudify \
    && echo 'pseudify ALL=(ALL) NOPASSWD:SETENV: ALL' >> /etc/sudoers \
    \
    \
    && cd /tmp \
    \
    && curl -o msodbcsql.apk $MSODBC_SQL_APK_URI \
    && curl -o mssql_tools.apk $MSSQL_TOOLS_APK_URI \
    \
    && curl -o msodbcsql.sig $MSODBC_SQL_SIG_URI \
    && curl -o mssql_tools.sig $MSSQL_TOOLS_SIG_URI \
    \
    && curl https://packages.microsoft.com/keys/microsoft.asc  | gpg --import - \
    && gpg --verify msodbcsql.sig msodbcsql.apk \
    && gpg --verify mssql_tools.sig mssql_tools.apk \
    \
    && apk add --allow-untrusted msodbcsql.apk \
    && apk add --allow-untrusted mssql_tools.apk \
    \
    && pecl install pdo_sqlsrv \
    && docker-php-ext-enable pdo_sqlsrv.so \
    \
    && pecl install sqlsrv \
    && docker-php-ext-enable sqlsrv.so \
    \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo_mysql \
        pdo_pgsql \
    \
    \
    && chmod +x /usr/local/bin/entrypoint \
    \
    \
    && mkdir -p /data \
    && chmod +rx /opt/pseudify/pseudify \
    \
    \
    && rm -rf /tmp/* \
    && rm -rf /var/cache/apk/* \
    && rm -rf /var/cache/distfiles/* \
    \
    && apk del --no-network \
        autoconf \
        dpkg-dev \
        dpkg \
        file \
        g++ \
        gcc \
        git \
        gnupg \
        libc-dev \
        make \
        pkgconf \
        re2c

WORKDIR /opt/pseudify

VOLUME ["/data"]

ENTRYPOINT ["/usr/local/bin/entrypoint"]

USER pseudify
