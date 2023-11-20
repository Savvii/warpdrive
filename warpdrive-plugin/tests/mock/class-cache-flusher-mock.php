<?php


namespace Mock;

/**
 * Class CacheFlusherMock
 *
 * Mock CacheFlusher
 *
 * @package mock
 */
class CacheFlusherMock implements \Savvii\CacheFlusherInterface
{

    /**
     * What should we return as result
     *
     * @var bool
     */
    private $flushResult = true;

    /**
     * Are we enabled ?
     *
     * @var bool
     */
    private $cacheEnabled = true;

    /**
     * @inheritDoc
     */
    public function flush()
    {
        return $this->flushResult;
    }

    /**
     * @inheritDoc
     */
    public function flush_domain($domain = null)
    {
        return $this->flushResult;
    }

    /**
     * @inheritDoc
     */
    public function is_enabled()
    {
        return $this->cacheEnabled;
    }
}