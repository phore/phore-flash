<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 21.05.19
 * Time: 15:20
 */

namespace Test;


use Phore\Core\Exception\InvalidDataException;
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

    public function testFlashLoadsMultipleTimes()
    {
        $flash = new Flash("redis://phore-flash_redis");

        $e = new Entity();
        $e->prop = "abc";

        $key = phore_random_str(16);
        $e->flash($flash, $key, 86400);

        $le = Entity::LoadFromFlash($flash, $key);
        $this->assertEquals("abc", $le->prop);

        $le = Entity::LoadFromFlash($flash, $key);
        $this->assertEquals("abc", $le->prop);

    }

    public function testFlashIsDeleted()
    {
        $flash = new Flash("redis://phore-flash_redis");

        $e = new Entity();
        $e->prop = "abc";

        $key = phore_random_str(16);
        $e->flash($flash, $key, 86400);

        $le = Entity::LoadFromFlash($flash, $key, true);
        $this->assertEquals("abc", $le->prop);

        $this->expectException(InvalidDataException::class);
        $le = Entity::LoadFromFlash($flash, $key);
        $this->assertEquals("abc", $le->prop);
    }


}
