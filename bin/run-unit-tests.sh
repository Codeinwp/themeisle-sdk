#!/usr/bin/env bash
bash bin/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
phpunit || exit 1
WP_MULTISITE=1 phpunit || exit 1
