<?php


namespace Savvii;


class CacheFlusherTest extends \Warpdrive_UnitTestCase
{

    /**
     * testcase Setup, construct a CacheFlusher with two CacheFlusherOpcaches in testmode
     */
    public function setUp()
    {
        // construct a CacheFlusher with 2 CacheFlusherOpcache()
        // which we can alter.

        $this->flusher = new CacheFlusher();
        $this->cache1 = new \Mock\CacheFlusherMock();
        $this->cache2 = new \Mock\CacheFlusherMock();

        // set the caches in CacheFlusher
        $this->setProtectedProperty($this->flusher, 'caches', [$this->cache1, $this->cache2]);
    }

    /**
     * Both caches should return true, so the combined result should also be true
     */
    public function test_successfull_flush_all_caches()
    {
        $this->assertTrue($this->flusher->flush());
    }

    /**
     * Both caches should return true, so the combined result should also be true
     */
    public function test_successfull_flush_domain_all_caches()
    {
        $this->assertTrue($this->flusher->flush_domain('example.com'));
    }

    /**
     * One cache returns false, so the combined result should be false
     */
    public function test_failed_flush_one_fail_caches()
    {
        // set the result of the second CacheFlusherOpcache to false
        $this->setProtectedProperty($this->cache2, 'flushResult', false);

        $this->assertFalse($this->flusher->flush());
    }

    /**
     * One cache returns false, so the combined result should be false
     */
    public function test_failed_flush_domain_one_fail_caches()
    {
        // set the result of the second CacheFlusherOpcache to false
        $this->setProtectedProperty($this->cache2, 'flushResult', false);

        $this->assertFalse($this->flusher->flush_domain('example.com'));
    }

    /**
     * Both caches return false, so the combined result should be false
     */
    public function test_failed_flush_both_fail_caches()
    {
        // set the result of the CacheFlusherOpcaches to false
        $this->setProtectedProperty($this->cache1, 'flushResult', false);
        $this->setProtectedProperty($this->cache2, 'flushResult', false);

        $this->assertFalse($this->flusher->flush());
    }

    /**
     * Both caches return false, so the combined result should be false
     */
    public function test_failed_flush_domain_both_fail_caches()
    {
        // set the result of the CacheFlusherOpcaches to false
        $this->setProtectedProperty($this->cache1, 'flushResult', false);
        $this->setProtectedProperty($this->cache2, 'flushResult', false);

        $this->assertFalse($this->flusher->flush_domain('example.com'));
    }

    /**
     * One cache is disabled (which returns a fail), should succeed
     */
    public function test_disabled_failed_cache_flush_caches()
    {
        // fail and disable a cache
        $this->setProtectedProperty($this->cache2, 'flushResult', false);
        $this->setProtectedProperty($this->cache2, 'cacheEnabled', false);

        $this->assertTrue($this->flusher->flush());
    }

    /**
     * One cache is disabled (which returns a fail), should succeed
     */
    public function test_disabled_failed_cache_flush_domain_caches()
    {
        // fail and disable a cache
        $this->setProtectedProperty($this->cache2, 'flushResult', false);
        $this->setProtectedProperty($this->cache2, 'cacheEnabled', false);

        $this->assertTrue($this->flusher->flush_domain('example.com'));
    }

    /**
     * All caches disabled, should succeed
     */
    public function test_successfull_flush_disabled_all_caches()
    {
        // disable both caches
        $this->setProtectedProperty($this->cache1, 'cacheEnabled', false);
        $this->setProtectedProperty($this->cache2, 'cacheEnabled', false);

        $this->assertTrue($this->flusher->flush());

    }

    /**
     * All caches disabled, should succeed
     */
    public function test_successfull_flush_domain_disabled_all_caches()
    {
        // disable both caches
        $this->setProtectedProperty($this->cache1, 'cacheEnabled', false);
        $this->setProtectedProperty($this->cache2, 'cacheEnabled', false);

        $this->assertTrue($this->flusher->flush_domain('example.com'));
    }
}