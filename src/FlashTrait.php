<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 21.05.19
 * Time: 12:32
 */

namespace Phore\Flash;


use Phore\Core\Exception\InvalidDataException;

trait FlashTrait
{

    public function flash(Flash $flash, string $key, int $ttl=null)
    {
        $flash = $flash->withSecureHash($key);
        if ($ttl !== null)
            $flash = $flash->withTTL($ttl);
        $flash->set($this);
    }

    /**
     * @param Flash $flash
     * @param string $key
     * @return FlashTrait
     */
    public static function LoadFromFlash(Flash $flash, string $key, bool $delete=false) : self
    {
        $flash = $flash->withSecureHash($key);
        $ret = $flash->get(null, self::class);
        if ($ret === null)
            throw new InvalidDataException("Flash key '$key' not found.");
        $key->del();
        return $ret;
    }
    
}
