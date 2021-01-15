<?php

namespace Savvii;

/**
 * Class SavviiCacheFlusher
 * Sends cache flush requests to all defined caches
 *
 */
class CacheFlusher implements CacheFlusherInterface {

    /**
     * the different caches we want to flush
     * @var string[]
     */
    private $caches = [
        'varnish', 'opcache',
        'memcached'
    ];

    /**
     * Array containg the initialized cache flushers
     * @var \stdClass[]
     */
    private $flusherClasses = [];

    /**
     * Constructor
     * @param array $args Options
     */
    public function __construct() {

        // Initialize the different caches
        foreach ($this->caches as $cache) {
            $className = '\Savvii\CacheFlusher' . ucfirst($cache);
            $this->flusherClasses[] = new $className();
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
        foreach ($this->flusherClasses as $cache) {
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
        foreach ($this->flusherClasses as $cache) {
            $result = $result && $cache->flush_domain($host);
        }

        return $result;
    }
}
