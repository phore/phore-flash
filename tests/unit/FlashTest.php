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
