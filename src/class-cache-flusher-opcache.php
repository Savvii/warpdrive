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
     * @inheritDoc
     */
    public function __construct()
    {
        // do nothing
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        return $this->flush_opcache();
    }

    /**
     * @inheritDoc
     */
    public function flush_domain($domain = null)
    {
        return $this->flush_opcache();
    }

    /**
     * Flush the OpCache if enabled
     */
    private function flush_opcache()
    {
        // early exit when in phpunittest
        if ($this->inTest) return $this->inTestResult;

        // default result
        $result = false;

        // get the status of the opcache
        $opcache_status = opcache_get_status();

        // flush it if enabled
        if ($opcache_status && is_array($opcache_status) &&
            array_key_exists('opcache_enabled', $opcache_status) &&
            $opcache_status['opcache_enabled']
        ) {
            $result = opcache_reset();
        }

        return $result;
    }
}