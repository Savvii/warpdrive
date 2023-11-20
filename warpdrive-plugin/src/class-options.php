<?php

namespace Savvii;

/**
 * Class Options
 * @package Savvii
 */
class Options {

    /**************************************************
     * Names consts
     **************************************************/

    const CACHING_STYLE                = 'warpdrive.caching_style';
    const CACHING_CUSTOM_POST_TYPES    = 'warpdrive_flush_custom_post_type';
    const REPO_LOCATION                = 'https://github.com/Savvii/warpdrive';
    const REPO_RELEASES_LOCATION       = 'https://api.github.com/repos/Savvii/warpdrive/releases/latest';
    const REPO_RELEASES_LATEST_ZIPBALL = 'https://github.com/Savvii/warpdrive/releases/latest/download/package.zip';
    const AVAILABLE_CACHES             = ['memcached', 'opcache', 'varnish', 'sucuri'];

    /**************************************************
     * Groups consts
     **************************************************/

    /**************************************************
     * Static access to options
     **************************************************/

    public static function api_location() {
        return self::env( 'WARPDRIVE_API' );
    }

    public static function access_token() {
        return self::env( 'WARPDRIVE_ACCESS_TOKEN' );
    }

    public static function system_name() {
        return self::env( 'WARPDRIVE_SYSTEM_NAME' );
    }

    /**
     * Wrapper around getenv()
     * When not found with getenv() also check $_ENV and $_SERVER.
     *
     * @param $name
     * @return array|false|mixed|string
     */
    private static function env($name) {
        return (getenv($name) ?:
            (array_key_exists($name, $_ENV) ?
                $_ENV[$name] : (array_key_exists($name, $_SERVER) ?
                    $_SERVER[$name] : '' )));
    }
}
