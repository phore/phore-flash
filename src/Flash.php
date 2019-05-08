<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.05.19
 * Time: 11:01
 */

namespace Phore\Flash;


use Phore\Flash\Driver\PhoreFlashDriver;
use Phore\Flash\Driver\RedisFlashDriver;

class Flash
{

    /**
     * @var PhoreFlashDriver
     */
    private $driver;

    private $prefix = null;
    private $key = null;
    private $ttl = null;


    /**
     * Flash constructor.
     * @param $driver   string|PhoreFlashDriver
     */
    public function __construct($driver)
    {
        if ($driver instanceof PhoreFlashDriver) {
            $this->driver = $driver;
            return;
        }

        if (is_string($driver)) {
            if (startsWith($driver, "redis://")) {
                $this->driver = new RedisFlashDriver($driver);
                return;
            }
        }
        throw new \InvalidArgumentException("Cannot interpret constructor parameter 1: '$driver'");
    }


    public function withPrefix(string $prefix) : self
    {
        if ($this->prefix !== null)
            throw new \InvalidArgumentException("withPrefix() Prefix already set.");
        $instance = clone $this;
        $instance->prefix = $prefix;
        return $instance;
    }


    public function withKey(string $key) : self
    {
        if ($this->key !== null)
            throw new \InvalidArgumentException("withKey() Cannot change prefix. Prefix is fix.");
        $instance = clone $this;
        $instance->key = $key;
        return $instance;
    }

    public function withQuickHash($key) : self
    {
        if ($this->key !== null)
            throw new \InvalidArgumentException("withPrefix() Cannot change prefix. Prefix is fix.");

        $instance = clone $this;
        $key = serialize($key);
        if (strlen($key) < 8)
            throw new \InvalidArgumentException("Not enough entropy characters in prefix");
        $instance->key = md5($key);
        return $instance;
    }

    public function withSecureHash ($key) : self
    {
        if ($this->key !== null)
            throw new \InvalidArgumentException("withSecurePrefix() Cannot change prefix. Prefix is fix.");

        $instance = clone $this;
        $key = serialize($key);
        if (strlen($key) < 40)
            throw new \InvalidArgumentException("Not enough entropy characters in prefix");
        $instance->key = sha1($key) . md5($key) . sha1($key . "P");
        return $instance;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function withTimeWindow(int $nseconds, int $offset=0) : self
    {
        if ($nseconds <= 0)
            throw new \InvalidArgumentException("Time window must be bigger than 0 seconds.");
        return $this->withExpiresAt((((int)(time() / $nseconds)) * $nseconds) + $nseconds + ($nseconds * $offset));
    }

    public function withExpiresAt(int $timestamp) : self
    {
        if ($this->ttl !== null)
            throw new \InvalidArgumentException("TTL already set. You are not allowed to set ttl and time Window");

        $instance = clone $this;
        $instance->ttl = $timestamp - time();
        return $instance;
    }

    public function withTTL (int $nseconds) : self
    {
        if ($this->ttl !== null)
            throw new \InvalidArgumentException("TTL already set. You are not allowed to set ttl and time Window");

        $instance = clone $this;
        $instance->ttl = $nseconds;
        return $instance;
    }

    public function exists() : bool
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");

        return $this->driver->exists($this->key);
    }

    public function get($default=null)
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        $ret = $this->driver->get($this->key);
        if ($ret === null)
            return $default;
        return $ret;
    }

    /**
     * Update existing key.
     *
     * Won't create a new key. Will return false if the key was not existing.
     *
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function update($data) : bool
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        return $this->driver->update($this->prefix . $this->key, $data, $this->ttl);
    }


    public function set($data)
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        $this->driver->set($this->prefix . $this->key, $data, $this->ttl);
    }

    public function incr(int $by = 1) : int
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        return $this->driver->incr($this->prefix . $this->key, $by, $this->ttl);
    }

    public function dec(int $by = 1) : int
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        return $this->driver->incr($this->prefix . $this->key, $by * -1, $this->ttl);
    }

    public function del() : bool
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        return $this->driver->del($this->prefix . $this->key);
    }

    public function lockWait(int $maxWait=1, int $maxWaitProc=1)
    {

    }

}
