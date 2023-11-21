#!/usr/bin/env bash

$WARDEN_BIN env exec wp-test bash -c "cd wp-content/plugins/warpdrive && vendor/bin/phpcs -d memory_limit=1024M -ps *.php src/*.php --standard=WordPress"
