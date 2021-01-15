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
     * CacheFlusherInterface constructor.
     */
    public function __construct();

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

}