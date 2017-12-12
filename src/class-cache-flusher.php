<?php

namespace Savvii;

/**
 * Class SavviiCacheFlusher
 * Sends cache flush request to the API
 */
class CacheFlusher {

    /**
     * Api object instance
     * @private Api
     */
    private $api;

    /**
     * Constructor
     * @param array $args Options
     */
    function __construct() {
        // Create SavviiApi instance for API communication
        $this->api = new Api();
    }

    /**
     * Flush cache
     * @return bool True on success
     */
    function flush() {
        // Flush the cache
        $result = $this->api->cache_flush();

        // Call API and check response code
        return $result->success();
    }

    /**
     * Flush cache for a specific domain
     * @return bool True on success
     */
    function flush_domain() {
        // Get the current domain
        $domain = wp_parse_url( get_site_url() );
        $host = isset( $domain['host'] ) ? $domain['host'] : '';

        // Flush the domain cache
        $result = $this->api->cache_flush( $host );

        // Call API and check response code
        return $result->success();
    }
}
