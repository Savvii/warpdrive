#!/usr/bin/env bash

DOMAIN=`grep TRAEFIK_DOMAIN .env | cut -d'=' -f2`
SUBDOMAIN=`grep TRAEFIK_SUBDOMAIN .env | cut -d'=' -f2`

$WARDEN_BIN env up
$WARDEN_BIN sign-certificate $SUBDOMAIN.$DOMAIN

# install wordpress
$WARDEN_BIN env exec php-fpm rm -rf wordpress
$WARDEN_BIN env exec php-fpm curl https://wordpress.org/latest.zip --output /tmp/wp.zip
$WARDEN_BIN env exec php-fpm unzip /tmp/wp.zip

# install wp-cli
$WARDEN_BIN env exec php-fpm sudo curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp
$WARDEN_BIN env exec php-fpm sudo chmod 755 /usr/local/bin/wp

# import clean wp db with admin:admin user
cat .warden/wordpress/wordpress.sql.gz | gunzip -c | $WARDEN_BIN db import

# activate warpdrive plugin
$WARDEN_BIN env exec php-fpm /usr/local/bin/wp plugin activate warpdrive


