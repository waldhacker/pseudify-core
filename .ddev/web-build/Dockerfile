RUN curl -L https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
        && curl -sLo /etc/apt/sources.list.d/mssql-release.list https://packages.microsoft.com/config/ubuntu/22.04/prod.list \
        && apt-get update \
        && ACCEPT_EULA=Y DEBIAN_FRONTEND=noninteractive apt-get install -y -o Dpkg::Options::='--force-confnew' --no-install-recommends \
            build-essential \
            mariadb-client \
            msodbcsql18 \
            mssql-tools18 \
            php8.1-dev \
            php-pear \
            postgresql-client \
            unixodbc-dev \
        \
        \
        && mv /opt/mssql-tools18 /opt/mssql-tools \
        && pecl install pdo_sqlsrv \
        && pecl install sqlsrv \
        \
        \
        && printf "; priority=20\nextension=sqlsrv.so\n" >/etc/php/8.1/mods-available/sqlsrv.ini \
        && printf "; priority=30\nextension=pdo_sqlsrv.so\n" >/etc/php/8.1/mods-available/pdo_sqlsrv.ini \
        && chmod 666 /etc/php/8.1/mods-available/*sqlsrv*.ini \
        && phpenmod -v 8.1 -s cli sqlsrv pdo_sqlsrv \
        && echo "xdebug.log_level=0" >> /etc/php/8.1/mods-available/xdebug.ini \
        \
        \
        && sed -i 's|include = /etc/php/8.1|include = /etc/php/8.0|g' /etc/php/8.1/fpm/php-fpm.conf \
        \
        \
        && echo 'Acquire::http::Timeout "90";' > /etc/apt/apt.conf.d/99timeout \
        && echo 'Acquire::ftp::Timeout "90";' >> /etc/apt/apt.conf.d/99timeout \
        && echo 'Acquire::Retries "10";' >> /etc/apt/apt.conf.d/99timeout \
        \
        \
        && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
            build-essential \
            php8.1-dev \
            unixodbc-dev \
        && rm -rf /var/lib/apt/lists/*
