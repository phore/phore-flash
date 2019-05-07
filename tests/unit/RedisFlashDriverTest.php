<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.05.19
 * Time: 16:27
 */

namespace Test;


use Phore\Flash\Driver\RedisFlashDriver;
use PHPUnit\Framework\TestCase;

class RedisFlashDriverTest extends TestCase
{

    public function testGetSet()
    {
        $driver = new RedisFlashDriver("redis://phore-flash_redis");

        $testData = "StringData";
        $driver->set("A", $testData);
        $this->assertEquals($testData, $driver->get("A"));

        $testData = 1234;
        $driver->set("A", $testData);
        $this->assertEquals($testData, $driver->get("A"));

        $testData = true;
        $driver->set("A", $testData);
        $this->assertEquals($testData, $driver->get("A"));

        $testData = ["some"=>"array"];
        $driver->set("A", $testData);
        $this->assertEquals($testData, $driver->get("A"));
    }


    public function testTimeout()
    {
        $driver = new RedisFlashDriver("redis://phore-flash_redis");
        $driver->set("B", "someData", 1);
        sleep(2);
        $this->assertEquals(null, $driver->get("B"));
    }


    public function testIncrmenet()
    {
        $driver = new RedisFlashDriver("redis://phore-flash_redis");
        $driver->del("C");
        $this->assertEquals(1, $driver->incr("C"));
        $this->assertEquals(2, $driver->incr("C"));
        $this->assertEquals(4, $driver->incr("C", 2));
    }

    public function testIncrementTimeout()
    {
        $driver = new RedisFlashDriver("redis://phore-flash_redis");
        $driver->incr("D", 1, 1);
        sleep(2);
        $this->assertEquals(null, $driver->get("D"));
    }

}
