<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 21.05.19
 * Time: 15:20
 */

namespace Test;


use Phore\Flash\Flash;
use Phore\Flash\FlashTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class Entity
 * @package Test
 * @internal
 */
class Entity {
    use FlashTrait;

    public $prop;
}

/**
 * Class FlashTraitTest
 * @package Test
 * @internal
 */
class FlashTraitTest extends TestCase
{

    public function testFlash()
    {
        $flash = new Flash("redis://phore-flash_redis");

        $e = new Entity();
        $e->prop = "abc";

        $key = phore_random_str(16);


        $e->flash($flash, $key, 86400);

        $le = Entity::LoadFromFlash($flash, $key);

        $this->assertEquals("abc", $le->prop);

    }


}
