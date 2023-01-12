#!/bin/bash

# **********************************************
# @version 0.0.1
# @author Ralf Zimmermann <hello@waldhacker.dev>
# **********************************************

if [ "$BASH" = "" ]; then echo "Error: you are not running this script within the bash."; exit 1; fi
if [ ! -x "$(command -v curl)" ]; then echo "Error: curl is not installed."; exit 1; fi
if [ ! -x "$(command -v php)" ]; then echo "Error: php is not installed."; exit 1; fi
if [ ! -x "$(command -v rsync)" ]; then echo "Error: rsync is not installed."; exit 1; fi

_THIS_SCRIPT_REAL_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
_ROOT_DIRECTORY=${_THIS_SCRIPT_REAL_PATH}/..

function install_composer
{
    mkdir -p "$_ROOT_DIRECTORY/.build/phar/tmp"
    cd "$_ROOT_DIRECTORY/.build/phar/tmp"

    local EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', '$_ROOT_DIRECTORY/.build/phar/tmp/composer-setup.php');"
    local ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '$_ROOT_DIRECTORY/.build/phar/tmp/composer-setup.php');")"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        >&2 echo '[BUILD] ERROR: Invalid installer checksum'
        rm -f "$_ROOT_DIRECTORY/.build/phar/tmp/composer-setup.php"
        exit 1
    fi

    php "$_ROOT_DIRECTORY/.build/phar/tmp/composer-setup.php" --quiet
    local RESULT=$?

    rm -f "$_ROOT_DIRECTORY/.build/phar/tmp/composer-setup.php"
    mv "$_ROOT_DIRECTORY/.build/phar/tmp/composer.phar" "$_ROOT_DIRECTORY/.build/phar/bin/composer"

    if [ $RESULT -gt 0 ]; then
        exit $RESULT
    fi
}

function prepare
{
    echo "[BUILD]: prepare"

    mkdir -p "$_ROOT_DIRECTORY/.build/phar/bin"
    mkdir -p "$_ROOT_DIRECTORY/.build/phar/dist"
    mkdir -p "$_ROOT_DIRECTORY/.build/phar/src"
    if [ ! -f "$_ROOT_DIRECTORY/.build/phar/bin/phar-composer.phar" ]; then
        curl -sJL -o "$_ROOT_DIRECTORY/.build/phar/bin/phar-composer.phar" https://clue.engineering/phar-composer-latest.phar
    fi
    if [ ! -x "$(command -v composer)" ] && [ ! -f "$_ROOT_DIRECTORY/.build/phar/bin/composer" ]; then
        install_composer
    fi
}

function build
{
    echo "[BUILD]: build"

    rsync -ravq --include="/bin/pseudify" --exclude="/bin/*" --exclude="/vendor" "$_ROOT_DIRECTORY/src/" "$_ROOT_DIRECTORY/.build/phar/src/"

    cd "$_ROOT_DIRECTORY/.build/phar/src/"
    if command -v composer; then
        composer install --no-dev --no-interaction
    else
        "$_ROOT_DIRECTORY/.build/phar/bin/composer" install --no-dev --no-interaction
    fi

    cd "$_ROOT_DIRECTORY/.build/phar/dist/"
    php --define phar.readonly=0 "$_ROOT_DIRECTORY/.build/phar/bin/phar-composer.phar" build "$_ROOT_DIRECTORY/.build/phar/src/"
    mv "$_ROOT_DIRECTORY/.build/phar/dist/pseudify.phar" "$_ROOT_DIRECTORY/.build/phar/dist/pseudify"
}

function cleanup
{
    echo "[BUILD]: cleanup"

    rm -rf "$_ROOT_DIRECTORY/.build/phar/src/"
    rm -rf "$_ROOT_DIRECTORY/.build/phar/tmp/"
}

function controller
{
    prepare
    build
    cleanup
}

controller

unset _THIS_SCRIPT_REAL_PATH
unset _ROOT_DIRECTORY
