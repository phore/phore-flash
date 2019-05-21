<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 07.05.19
 * Time: 15:43
 */

namespace Test;


use Phore\Flash\Driver\RedisFlashDriver;
use Phore\Flash\Flash;
use PHPUnit\Framework\TestCase;

/**
 * Class FlashTest
 * @package Test
 * @internal
 */
class FlashTest extends TestCase
{

    public function testPrefix()
    {
        $flash = new Flash("redis://phore-flash_redis");
        $flash = $flash->withPrefix("SomePrefix");

        $key = phore_random_str(16);

        $flash->withSecureHash($key)->set("abc");

        $this->assertEquals("abc", $flash->withSecureHash($key)->get());
    }

    public function testSetDifferentDataTypes()
    {
        $flash = new Flash("redis://phore-flash_redis");

        $key = $flash->withQuickHash("muh");

        $key->set("SimpleData");
        $this->assertEquals("SimpleData", $key->get());

        $key->set(["array" => "data"]);
        $this->assertEquals(["array" => "data"], $key->get());

    }


}
