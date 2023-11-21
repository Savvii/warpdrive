#!/usr/bin/env bash

$WARDEN_BIN env exec wp-test bash -c "cd wp-content/plugins/warpdrive && composer test"
