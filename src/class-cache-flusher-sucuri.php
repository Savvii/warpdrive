<?php

namespace Savvii;

/**
 * Class SavviiCacheSucuri
 * Sends Sucuri cache flush request to the API
 */
class CacheFlusherSucuri implements CacheFlusherInterface {

    /**
     * Are we in a test
     *
     * @var bool
     */
    protected $inTest = false;

    /**
     * Do we override the is_enabled() function
     *
     * @var bool
     */
    protected $overrideIsEnabled = false;

    /**
     * What is the result of is_enabled() when overridden
     *
     * @var bool
     */
    protected $overrideIsEnabledResult = true;

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
        // test override (not pretty) TODO: rewrite this
        if ($this->inTest && $this->overrideIsEnabled) return $this->overrideIsEnabledResult;

        $result = $this->api->sucuri_cache_is_enabled();
        $bodyRaw = $result->get_body();

        if ($result->success() && !empty($bodyRaw)
        ) {
            $enabled = json_decode($bodyRaw);

            if (!is_null($enabled)) {
                return $enabled;
            }
        }

        return false;
    }
}
