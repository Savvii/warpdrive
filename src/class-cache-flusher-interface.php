<?php


namespace Savvii;


/**
 * Interface CacheFlusherInterface
 *
 * We expect the following methods in CacheFlusher class so make sure we implement them.
 *
 * @package Savvii
 */
interface CacheFlusherInterface
{
    /**
     * Flush cache for all sites
     *
     * @return bool
     */
    public function flush();

    /**
     * Flush cache for a specific site/domain
     *
     * @param null $domain
     * @return bool
     */
    public function flush_domain($domain = null);

    /**
     * Returns if the current cache is enabled.
     *
     * @return bool
     */
    public function is_enabled();

}