#!/bin/bash

# **********************************************
# @version 0.0.1
# @author Ralf Zimmermann <hello@waldhacker.dev>
# **********************************************

if [ "$BASH" = "" ]; then echo "Error: you are not running this script within the bash."; exit 1; fi
if [ ! -x "$(command -v docker)" ]; then echo "Error: docker is not installed."; exit 1; fi

_THIS_SCRIPT_REAL_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
_ROOT_DIRECTORY="${_THIS_SCRIPT_REAL_PATH}/.."
_IMAGE_NAMESPACE=${IMAGE_NAMESPACE:-ghcr.io/waldhacker/pseudify}

function prepare
{
    echo "[BUILD]: prepare"

    mkdir -p "$_ROOT_DIRECTORY/.build/docker/context"
}

function build
{
    local PHP_VERSION=8.1
    # https://learn.microsoft.com/de-de/sql/connect/odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver15#alpine18
    local MSODBC_SQL_APK_URI=https://download.microsoft.com/download/b/9/f/b9f3cce4-3925-46d4-9f46-da08869c6486/msodbcsql18_18.1.1.1-1_amd64.apk
    local MSSQL_TOOLS_APK_URI=https://download.microsoft.com/download/b/9/f/b9f3cce4-3925-46d4-9f46-da08869c6486/mssql-tools18_18.1.1.1-1_amd64.apk
    local MSODBC_SQL_SIG_URI=https://download.microsoft.com/download/b/9/f/b9f3cce4-3925-46d4-9f46-da08869c6486/msodbcsql18_18.1.1.1-1_amd64.sig
    local MSSQL_TOOLS_SIG_URI=https://download.microsoft.com/download/b/9/f/b9f3cce4-3925-46d4-9f46-da08869c6486/mssql-tools18_18.1.1.1-1_amd64.sig

    if [ ! -z "$CI_COMMIT_TAG" ]; then
        local BUILD_TAG=$CI_COMMIT_TAG
    elif [ ! -z "$GITHUB_REF_NAME" ]; then
        local BUILD_TAG=$GITHUB_REF_NAME
    else
        local BUILD_TAG=$(git rev-parse --short HEAD)
    fi

    echo "[BUILD]: build $BUILD_TAG"
    echo "[BUILD]: use PHP $PHP_VERSION"
    echo "[BUILD]: use Microsoft ODBC Driver $(basename $MSODBC_SQL_APK_URI .apk)"
    echo "[BUILD]: use Microsoft SQL Server Tools $(basename $MSSQL_TOOLS_APK_URI .apk)"

    rsync -ravq "$_ROOT_DIRECTORY/build/docker/" "$_ROOT_DIRECTORY/.build/docker/context/"
    cp "$_ROOT_DIRECTORY/.build/phar/dist/pseudify" "$_ROOT_DIRECTORY/.build/docker/context/pseudify"

    docker build \
        --pull \
        --no-cache \
        --build-arg PHP_VERSION=$PHP_VERSION \
        --build-arg MSODBC_SQL_APK_URI=$MSODBC_SQL_APK_URI \
        --build-arg MSSQL_TOOLS_APK_URI=$MSSQL_TOOLS_APK_URI \
        --build-arg MSODBC_SQL_SIG_URI=$MSODBC_SQL_SIG_URI \
        --build-arg MSSQL_TOOLS_SIG_URI=$MSSQL_TOOLS_SIG_URI \
        --build-arg BUILD_TAG=$BUILD_TAG \
        -t ${_IMAGE_NAMESPACE}:$BUILD_TAG \
        -t ${_IMAGE_NAMESPACE}:latest \
        --file "$_ROOT_DIRECTORY/build/Dockerfile" \
        "$_ROOT_DIRECTORY/.build/docker/context"
}

function cleanup
{
    echo "[BUILD]: cleanup"

    rm -rf "$_ROOT_DIRECTORY/.build/docker/"
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
unset _IMAGE_NAMESPACE
