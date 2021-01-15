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
        $this->cache1 = new CacheFlusherOpcache();
        $this->cache2 = new CacheFlusherOpcache();

        // enable testing behaviour in the CacheFlusherOpcaches
        $this->setProtectedProperty($this->cache1, 'inTest', true);
        $this->setProtectedProperty($this->cache2, 'inTest', true);

        // set the caches in CacheFlusher
        $this->setProtectedProperty($this->flusher, 'flusherClasses', [$this->cache1, $this->cache2]);
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
        $this->setProtectedProperty($this->cache2, 'inTestResult', false);

        $this->assertFalse($this->flusher->flush());
    }

    /**
     * One cache returns false, so the combined result should be false
     */
    public function test_failed_flush_domain_one_fail_caches()
    {
        // set the result of the second CacheFlusherOpcache to false
        $this->setProtectedProperty($this->cache2, 'inTestResult', false);

        $this->assertFalse($this->flusher->flush_domain('example.com'));
    }

    /**
     * Both caches return false, so the combined result should be false
     */
    public function test_failed_flush_both_fail_caches()
    {
        // set the result of the CacheFlusherOpcaches to false
        $this->setProtectedProperty($this->cache1, 'inTestResult', false);
        $this->setProtectedProperty($this->cache2, 'inTestResult', false);

        $this->assertFalse($this->flusher->flush());
    }

    /**
     * Both caches return false, so the combined result should be false
     */
    public function test_failed_flush_domain_both_fail_caches()
    {
        // set the result of the CacheFlusherOpcaches to false
        $this->setProtectedProperty($this->cache1, 'inTestResult', false);
        $this->setProtectedProperty($this->cache2, 'inTestResult', false);

        $this->assertFalse($this->flusher->flush_domain('example.com'));
    }

}