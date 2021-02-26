<?php


namespace Savvii;


class CacheFlusherOpcache implements CacheFlusherInterface
{
    /**
     * Used to override the behaviour in the flush_opcache() method during unittests
     * (workaround, 'cause we can't disable and enable opcache on the fly
     *
     * @var bool
     */
    protected $inTest = false;

    /**
     * Return value of flush_opcache() when overridden
     * @var bool
     */
    protected $inTestResult = true;

    /**
     * Return value of is_enabled() when overridden
     * @var bool
     */
    protected $inTestEnabled = true;

    /**
     * Flush the Opcache
     *
     * @returns bool
     */
    public function flush()
    {
        return $this->flush_opcache();
    }

    /**
     * Flush the Opcache for a specific domain
     * Return true, because we can only flush the entire opcache and
     * not a part for a specific site
     *
     * @returns bool
     */
    public function flush_domain($domain = null)
    {
        return true;
    }

    /**
     * Flush the OpCache if enabled
     */
    private function flush_opcache()
    {
        // early exit when in phpunittest
        if ($this->inTest) return $this->inTestResult;

        // default result
        $result = true;

        // flush it, if enabled
        if ($this->is_enabled()) {
            $result = opcache_reset();
        }

        return $result;
    }

    /**
     * Check if the Opcache is enabled
     *
     * @return bool
     */
    public function is_enabled()
    {
        // early exit when in phpunittest
        if ($this->inTest) return $this->inTestEnabled;

        // check if 'Zend OPcache' is loaded early exit when not
        if(!extension_loaded('Zend OPcache')) return false;

        // get the status of the opcache
        $opcache_status = opcache_get_status();

        // check and return the status of the opcache
        return ($opcache_status && is_array($opcache_status) &&
            array_key_exists('opcache_enabled', $opcache_status) &&
            $opcache_status['opcache_enabled']
        );
    }
}