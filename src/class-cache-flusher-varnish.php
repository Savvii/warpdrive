<?php

namespace Savvii;

/**
 * Class SavviiCacheFlusher
 * Sends cache flush request to the API
 */
class CacheFlusherVarnish implements CacheFlusherInterface {

    /**
     * Api object instance
     * @private Api
     */
    private $api;

    /**
     * Constructor
     * @param array $args Options
     */
    public function __construct() {
        // Create SavviiApi instance for API communication
        $this->api = new Api();
    }

    /**
     * Flush cache
     * @return bool True on success
     */
    public function flush() {
        // Flush the cache
        $result = $this->api->cache_flush();

        // Call API and check response code
        return $result->success();
    }

    /**
     * Flush cache for a specific domain
     * @param null $domain
     * @return bool True on success
     */
    public function flush_domain($domain = null) {

        // Flush the domain cache
        $result = $this->api->cache_flush( $domain );

        // Call API and check response code
        return $result->success();
    }

    /**
     * The Varnish cache is always enabled
     *
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }

}
