<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.05.19
 * Time: 10:39
 */

namespace Phore\Flash\Driver;


interface PhoreFlashDriver
{

    /**
     * Return the value of the key
     *
     * If Key isn't exsiting or data is not readable,
     * return null
     *
     * @param $key
     * @return array|null
     */
    public function get($key);


    /**
     * Create or update data.
     *
     * @param string $key
     * @param array $data
     * @param int $ttl
     * @return bool
     */
    public function set(string $key, $data, int $ttl=null) : bool;


    /**
     * Update data.
     *
     * Will not create a new dataset.
     *
     *
     * @param string $key
     * @param $data
     * @return bool
     */
    public function update(string $key, $data) : bool;


    /**
     * Increment/Decrement the key and return the new value
     *
     * First increment on new keys will return 1
     *
     * @param int $inc
     * @return int The new value of key
     */
    public function incr(string $key, int $inc=1, int $ttl=null) : int;

    /**
     * Delete a key
     *
     * @param string $key
     * @return bool
     */
    public function del(string $key) : bool;

    /**
     * Check if the key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key) : bool;
}
