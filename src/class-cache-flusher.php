<?php

namespace Savvii;

/**
 * Class SavviiCacheFlusher
 * Sends cache flush requests to all defined caches
 *
 */
class CacheFlusher implements CacheFlusherInterface {

    /**
     * Array containg the initialized cache flushers
     * @var object[]
     */
    private $caches = [];

    /**
     * Constructor
     * @param array $args Options
     */
    public function __construct() {

        // Initialize the different caches
        foreach (Options::AVAILABLE_CACHES as $cache) {
            $className = '\Savvii\CacheFlusher' . ucfirst($cache);
            $this->caches[] = new $className();
        }
    }

    /**
     * Flush all caches
     * @return bool True on success
     */
    public function flush() {
        $result = true;

        // Loop over all caches and flush them
        // bitwise and the results with the current result
        // so if a cache isn't flushed we return false
        foreach ($this->caches as $cache) {

            // only flush enabled caches
            if (!$cache->is_enabled()) continue;

            $result = $result && $cache->flush();
        }
      
        return $result;
    }

    /**
     * Flush cache for a specific domain
     * @param null $domain
     * @return bool True on success
     */
    public function flush_domain($domain = null) {
        $result = true;

        // Get the current domain
        $siteDomain = wp_parse_url( get_site_url() );
        $host = isset( $domain['host'] ) ? $siteDomain['host'] : '';

        // Loop over all caches and flush them
        // bitwise and the results with the current result
        // so if a cache isn't flushed we return false
        foreach ($this->caches as $cache) {

            // only flush enabled caches
            if (!$cache->is_enabled()) continue;

            $result = $result && $cache->flush_domain($host);
        }
        return $result;
    }

    /**
     * The interface forces us to have an is_enabled() method :)
     *
     * @return bool
     */
    public function is_enabled()
    {
        if (extension_loaded('Zend OPcache')) {
            {
                $opcache_status = opcache_get_status();

                if ($opcache_status && is_array($opcache_status) &&
                    array_key_exists('opcache_enabled', $opcache_status) &&
                    $opcache_status['opcache_enabled']
                ) {
                    opcache_reset();
                }
            }
        }
    }
}
