#!/bin/bash

DIR=`dirname $0`;

composer install --no-interaction --prefer-source

$DIR/../vendor/bin/tester -p php $DIR/src -s -j 1 --colors 1 -c $DIR/php_unix.ini