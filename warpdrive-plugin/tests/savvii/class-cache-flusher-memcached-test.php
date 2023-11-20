<?php


namespace Savvii;


class CacheFlusherMemcachedTest extends \Warpdrive_UnitTestCase
{
    /**
     * @var CacheFlusherMemcached
     */
    private $cache;

    /**
     * @var resource
     */
    private $testStream;

    /**
     * Testcase setUp(), create CacheFlusherMemcached and overwrite the stream
     */
    public function setUp()
    {
        // new CacheFlusherMemcached
        $this->cache = new CacheFlusherMemcached();

        // create replacement stream
        $this->testStream = fopen('php://memory', 'r+');

        // write test data to stream and rewind it
        fputs($this->testStream, "flush_all\r\nOK\r\n");
        rewind($this->testStream);

        // replace the stream in the CacheFlusher
        $this->setProtectedProperty($this->cache, 'memcachedStream', $this->testStream);
    }

    /**
     * The teststream should return 'OK', so flush() should return true
     */
    public function test_successfull_flush()
    {
        $this->assertTrue($this->cache->flush());
    }

    /**
     * The teststream should return 'OK', so flush_domain() should return true
     */
    public function test_successfull_flush_domain()
    {
        $this->assertTrue($this->cache->flush_domain('example.com'));
    }

    /**
     * We set the teststream to return 'ERROR', so flush() should return false
     */
    public function test_failed_flush()
    {
        // write error data to testStream
        fputs($this->testStream, "flush_all\r\nERROR\r\n");
        rewind($this->testStream);

        // expect false
        $this->assertFalse($this->cache->flush());
    }

    /**
     * We set the teststream to return 'ERROR'
     * flush_domain() should still give a success, as memcached can't flush a single domain
     */
    public function test_always_success_flush_domain()
    {
        // write error data to testStream
        fputs($this->testStream, "flush_all\r\nERROR\r\n");
        rewind($this->testStream);

        // expect true even, if we give an error back :)
        $this->assertTrue($this->cache->flush_domain('example.com'));
    }

    /**
     * We set the teststream to false, to emulate a not connected socketstream.
     * If memcached isn't running on the host, CacheFlusherMemcached() shouldn't be able to connect to it.
     * And when it isn't running, we should give a successfull state back,
     * as we can't flush a non existing cache
     */
    public function test_successfull_no_memcached_flush()
    {
        // disable connection to memcached
        $this->setProtectedProperty($this->cache, 'memcachedStream', false);

        // expect true, if we can't connect to memcached
        $this->assertTrue($this->cache->flush());
    }

    /**
     * We set the teststream to false, to emulate a not connected socketstream.
     * If memcached isn't running on the host, CacheFlusherMemcached() shouldn't be able to connect to it.
     * And when it isn't running, we should give a successfull state back,
     * as we can't flush a non existing cache
     */
    public function test_successfull_no_memcached_flush_domain()
    {
        // disable connection to memcached
        $this->setProtectedProperty($this->cache, 'memcachedStream', false);

        // expect true, if we can't connect to memcached
        $this->assertTrue($this->cache->flush_domain('example.com'));
    }

    /**
     * Check successfull is_enabled()
     */
    public function test_success_is_enabled()
    {
        $this->assertTrue($this->cache->is_enabled());
    }

    /**
     * Check unsuccessfull is_enabled()
     */
    public function test_unsuccessfull_is_enabled()
    {
        $this->setProtectedProperty($this->cache, 'memcachedStream', false);
        $this->assertFalse($this->cache->is_enabled());
    }
}