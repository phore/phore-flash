<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.05.19
 * Time: 11:22
 */

namespace Phore\Flash\Driver;


class RedisFlashDriver implements PhoreFlashDriver
{

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * RedisFlashDriver constructor.
     * @param $connect  \Redis|string
     * @param int $dbindex
     * @throws \Exception
     */
    public function __construct($connect, int $dbindex=0)
    {
        if ($connect instanceof \Redis) {
            $this->redis = $connect;
            return;
        }
        if (is_string($connect)) {
            $uri = parse_url($connect);
            if ( ! isset ($uri["scheme"]) || $uri["scheme"] !== "redis")
                throw new \InvalidArgumentException("Invalid scheme. Format: redis://[passwd@]<host>?<options>");
            if ( ! isset ($uri["host"]))
                throw new \InvalidArgumentException("Invalid host. Format: redis://[passwd@]<host>?<options>");
            $host = $uri["host"];

            $pass = null;
            if (isset ($uri["pass"]))
               $pass = $uri["pass"];

            $this->redis = new \Redis();
            if (!$this->redis->pconnect($host))
                throw new \Exception("Can't connect redis server '$host'.");
            if ($pass) {
                if ( ! $this->redis->auth($pass)) {
                    throw new \Exception("Authentication to redis server on host '$host' failed.");
                }
            }
            if (!$this->redis->select($dbindex))
                throw new \Exception("Can't select database '$dbindex'");
            return;
        }
        throw new \InvalidArgumentException("Can't handle parameter 1 type " . gettype($connect));

    }


    public function get($key)
    {
        $ret = $this->redis->get($key);
        if ($ret === false)
            return null;
        return $ret;
    }


    public function set(string $key, $data, int $ttl=null) : bool
    {
        if ($this->redis->setnx($key, $data)) {
            if ($ttl > 0) {
                // Only set expire on new entry
                $this->redis->expire($key, $ttl);
            }
            return true;
        }
        if ( ! $this->redis->set($key, $data))
            throw new \Exception("Cannot set data. Redis error: " . $this->redis->getLastError());
        return true;
    }


    /**
     * Update only if key exists.
     *
     * @param string $key
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function update(string $key, $data) : bool
    {
        if ( ! $this->redis->exists($key))
            return false;
        if ( ! $this->redis->set($key, $data))
            throw new \Exception("Cannot set data. Redis error: " . $this->redis->getLastError());
        return true;
    }


    /**
     * Increment/Decrement the key and return the new value
     *
     * First increment on new keys will return 1
     *
     * @param int $inc
     * @return int The new value of key
     */
    public function incr(string $key, int $inc=1, int $ttl=null) : int
    {
        $ret = $this->redis->incrBy($key, $inc);
        if ($ret == $inc && $ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
        return $ret;
    }


    public function del(string $key) : bool
    {
        if ($this->redis->delete($key) === 1)
            return true;
        return false;
    }


    public function exists(string $key) : bool
    {
        return $this->redis->exists($key);
    }

}
