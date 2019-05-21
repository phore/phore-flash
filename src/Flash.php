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


    private $allowClasses = [];
    
    
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

    /**
     * @param array $classes
     * @return self
     */
    public function allowClass($classes) : self 
    {
        if ( ! is_array($classes) && ! is_string($classes))
            throw new \InvalidArgumentException("Parameter 1 expects to be array or string.");
        if ( ! is_array($classes))
            $classes = [$classes];
        $this->allowClasses += $classes;
        return $this;
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
        if (strlen($key) < 16)
            throw new \InvalidArgumentException("Not enough entropy characters in prefix (min 16 chars)");
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

        return $this->driver->exists($this->prefix . $this->key);
    }
    
    public function get($default=null, string $expectClass=null)
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        $ret = $this->driver->get($this->prefix . $this->key);
        if ($ret === null) {
            if ($default instanceof \Exception)
                throw $default;
            return $default;
        }

        $allowedClasses = $this->allowClasses;
        if ($expectClass !== null)
            $allowedClasses[] = $expectClass;
        $ret = unserialize($ret, ["allowed_classes" => $allowedClasses]);
        if ($ret === false)
            throw new \InvalidArgumentException("Cannot unserialize flash-data.");
        if ($expectClass !== null) {
            if (!$ret instanceof $expectClass) {
                throw new \InvalidArgumentException("Expected class: '$expectClass' but " . print_r ($ret, true) . " found.");
            }
        }
        return $ret;
    }

    
    protected function _validateData($data)
    {
        if ( ! $this->isAllowed($data))
            throw new \InvalidArgumentException("Object of class '$className' is not allowed. Call Flash::allowClassed() to add it.");
    }


    public function isAllowed($value) : bool
    {
        if (is_object($value)) {
            $className = get_class($value);
            if (! in_array($className, $this->allowClasses))
                return false;
        }
        return true;
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
        
        return $this->driver->update($this->prefix . $this->key, serialize($data), $this->ttl);
    }


    public function set($data)
    {
        if ($this->key === null)
            throw new \InvalidArgumentException("No key set. Use Flash::withXy() to select storage keys.");
        $this->driver->set($this->prefix . $this->key, serialize($data), $this->ttl);
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
