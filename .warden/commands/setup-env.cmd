#!/usr/bin/env bash

DOMAIN=`grep TRAEFIK_DOMAIN .env | cut -d'=' -f2`
SUBDOMAIN=`grep TRAEFIK_SUBDOMAIN .env | cut -d'=' -f2`

$WARDEN_BIN env up
$WARDEN_BIN sign-certificate $SUBDOMAIN.$DOMAIN

# create wordpress_test database
echo "Creating test database"
cat .warden/wordpress_test/wordpress_test.sql | $WARDEN_BIN db import -u root

# install wordpress
echo "Installing wordpress"
$WARDEN_BIN env exec php-fpm rm -rf wordpress
$WARDEN_BIN env exec php-fpm curl -s https://wordpress.org/latest.zip --output /tmp/wp.zip
$WARDEN_BIN env exec php-fpm unzip /tmp/wp.zip > /dev/null

# install wp-cli
echo "Installing wp-cli"
$WARDEN_BIN env exec php-fpm sudo curl -s https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp
$WARDEN_BIN env exec php-fpm sudo chmod 755 /usr/local/bin/wp

# import clean wp databases with admin:admin user
echo "Importing databases"
cat .warden/wordpress/wordpress.sql.gz | gunzip -c | $WARDEN_BIN db import
cat .warden/wordpress/wordpress.sql.gz | gunzip -c | $WARDEN_BIN db import wordpress_test

# activate warpdrive plugin
echo "Activating warpdrive"
$WARDEN_BIN env exec php-fpm /usr/local/bin/wp plugin activate warpdrive

# install_wp_tests
echo "Installing test suite"
$WARDEN_BIN env exec wp-test bash -c "yes | /usr/local/bin/install-wp-tests > /dev/null"
$WARDEN_BIN env exec wp-test composer install



