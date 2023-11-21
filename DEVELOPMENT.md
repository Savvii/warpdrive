## Setting up your environment

### Requirements

 * [Warden](https://warden.dev) 
 * PHP 7.4, PHP 8.*

### Setup

Firstly get a local copy of the repository using git clone.

    git clone git@github.com:Savvii/warpdrive.git

Before starting development we recommend creating a branch from "master" where you can push your code to.
This will give you your own branch to commit your changes to.

    git checkout master
    git checkout -b my-feature-branch master

#### Copy warden config
We use `warden.env` as a base, you can copy it to `.env`

    cp warden.env .env

#### Start Warden environment
Warden creates a container which runs wordpress with warpdrive installed, this wordpress is accessible on https://warpdrive.savvii.test with username `admin` and password `admin`.
It also creates a `wp-test` container in which you can run the PHPUnit tests.

    warden setup-env

Both containers can be accessed in warden eg to run tests manually 

    # php-fpm (wordpress site)
    warden env exec php-fpm bash 

    # wp-test (unit tests)
    warden env exec wp-test bash

## Testing

Warpdrive uses [PHPUnit 9.6.*](https://phpunit.de/) to run automated tests. The test suite can be run through warden

    warden run-test

This will execute both the single site unittests and the multisite tests.

## Coding standards

Should be fixed and updated 
