<?php

namespace Savvii;

/**
 * Class SavviiCacheSucuri
 * Sends Sucuri cache flush request to the API
 */
class CacheFlusherSucuri implements CacheFlusherInterface {

    /**
     * Api object instance
     * @private Api
     */
    private $api;

    /**
     * Constructor
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
        // Early return if not enabled
        if (!$this->is_enabled()) return true;

        // Flush the cache
        $result = $this->api->sucuri_cache_flush();

        // Call API and check response code
        return $result->success();
    }

    /**
     * Flush cache for a specific domain
     * @param null $domain
     * @return bool True on success
     */
    public function flush_domain($domain = null) {
        // Early return if not enabled
        if (!$this->is_enabled()) return true;

        // Flush the domain cache
        $result = $this->api->sucuri_cache_flush( $domain );

        // Call API and check response code
        return $result->success();
    }

    /**
     * Check if the Sucuri cache is enabled
     *
     * @return bool
     */
    public function is_enabled()
    {
        $result = $this->api->sucuri_cache_is_enabled();
        $response = $result->get_response();

        if ($result->success() && is_array($response) &&
            array_key_exists('body', $response)
        ) {
            $body = json_decode($response['body']);
            if (!is_null($body)) {
                return $body->enabled;
            }
        }

        return false;
    }
}
