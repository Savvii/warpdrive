<?php


namespace Savvii;


class CacheFlusherMemcached implements CacheFlusherInterface
{

    /**
     * @var false|resource
     */
    private $memcachedStream;

    /**
     * @var integer
     * Contains the error code, if the socket stream can't connect
     */
    private $errCode;

    /**
     * @var string
     * Contains the error message, if the socket can't connect
     */
    private $errMsg;

    /**
     * CacheFlusherMemcached constructor.
     * Set up the stream to the menmcached server
     */
    public function __construct()
    {
        // TODO: how is the memcached setup in a shared vps ?
        // my guess would be, that each wp instance has its own memcached on a different port, needs checking
        // and implement a multiuser solution

        $this->memcachedStream =
            @stream_socket_client("tcp://127.0.0.1:11211", $this->errCode, $this->errMsg, 1);
    }

    /**
     * Make house and clean up
     * Close the stream to memcached
     */
    public function __destruct()
    {
        if ($this->memcachedStream !== false) {
            fclose($this->memcachedStream);
        }
    }

    /**
     * Call flush_memcached() to flush all
     *
     * @return bool
     */
    public function flush()
    {
        return $this->flush_memcached();
    }

    /**
     * We can't flush a specific domain, so flush everything
     *
     * @param string $domain
     * @return bool
     */
    public function flush_domain($domain = null)
    {
        return $this->flush_memcached();
    }

    /**
     * As memcached doesn't work with wildcards or namespaces, we can only flush everything.
     * So we send a 'flush_all' to memcached and expect an 'OK' back.
     *
     * @return bool
     */
    private function flush_memcached()
    {
        // if we can't connect to the socket (no memcached running), we 'succeed')
        if (!$this->memcachedStream) return true;

        // connection to memcached
        // send 'flush_all' and wait for 'OK'
        fwrite($this->memcachedStream, "flush_all\r\n");
        $response = fgets($this->memcachedStream, 1024);

        return (preg_match('/^OK/', $response) == 1);
    }
}