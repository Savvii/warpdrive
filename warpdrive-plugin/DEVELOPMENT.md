## Setting up your environment

### Requirements

 * [Code Climate](https://github.com/codeclimate/codeclimate)
 * [Composer](https://getcomposer.org/)
 * PHP 5.6, PHP 7.*
 * [Latest WordPress version](https://wordpress.org/download/release-archive/)

### Setup

Firstly get a local copy of the repository using git clone.

    git clone git@github.com:Savvii/warpdrive.git

Before starting development we recommend creating a branch from "master" where you can push your code to.
This will give you your own branch to commit your changes to.

    git checkout master
    git checkout -b my-feature-branch master

In order to use the [PHPunit](https://phpunit.de/) test suite you have to install it using [composer](https://getcomposer.org/) with the following command.

    composer install

To install the WordPress used for testing you can use the "install-wp-tests.bash" script.

    # NOTE: This script is not Windows compatible
    # usage: tests/install-wp-tests.bash <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
    bash tests/install-wp-tests.bash warpdrive warpdrive insecure localhost latest false

The above mentioned script will create a WordPress installation in your systems `/tmp` directory and set up the associated database to be used for the PHPUnit tests. If the installation gets cleared after a reboot you can rerun this command with the `[skip-database-creation]` option set to `true`.

## Testing

Warpdrive uses [PHPUnit 5.7.*](https://phpunit.de/) to run automated tests.
For documentation refer to [DevDocs](http://devdocs.io/phpunit~5/).
You can switch between normal tests and multisite tests using the `WP_MULTISITE` environment variable.

    WP_MULTISITE=0 vendor/phpunit/phpunit/phpunit
    WP_MULTISITE=1 vendor/phpunit/phpunit/phpunit -c phpunit-multisite.xml
    # Optionally create a test coverage report
    WP_MULTISITE=1 vendor/phpunit/phpunit/phpunit -c phpunit-multisite.xml --coverage-html=/tmp/warpdrive-coverage

## Coding standards

We use the [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/) with the small modification that we use *spaces* instead of *real tabs*.
The codestyle can be checked using [Code Climate CLI](https://github.com/codeclimate/codeclimate).

    codeclimate analyze # This will run all the engines for the codestyle checks
    # To run a specific engine using you can use the -e flag
    codeclimate analyze -e phpmd
