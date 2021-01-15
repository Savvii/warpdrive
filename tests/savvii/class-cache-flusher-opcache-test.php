<?php


namespace Savvii;


class CacheFlusherOpcacheTest extends \Warpdrive_UnitTestCase
{

    protected $cache;

    /**
     * testcase setUp(), create an testing enabled CacheFlusherOpcache class
     */
    public function setUp()
    {
        parent::setUp();
        $this->cache = new CacheFlusherOpcache();
        $this->setProtectedProperty($this->cache, 'inTest', true);
    }

    /**
     * Should return true
     */
    public function test_successfull_flush()
    {
        $this->assertTrue($this->cache->flush());
    }

    /**
     * Should return true
     */
    public function test_successfull_flush_domain()
    {
        $this->assertTrue($this->cache->flush_domain('example.com'));
    }

    /**
     * Flush should fail
     */
    public function test_unsuccessfull_flush()
    {
        // let the flush fail
        $this->setProtectedProperty($this->cache, 'inTestResult', false);

        $this->assertFalse($this->cache->flush());
    }

    /**
     * Flush should fail
     */
    public function test_unsuccessfull_flush_domain()
    {
        // let the flush fail
        $this->setProtectedProperty($this->cache, 'inTestResult', false);

        $this->assertFalse($this->cache->flush_domain('example.com'));
    }
}