<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 29.05.19
 * Time: 10:25
 */

namespace Phore\Flash;


use Phore\MicroApp\App;
use Phore\MicroApp\AppModule;

class FlashModule implements AppModule
{

    private $driver;


    /**
     *
     * <example>
     *
     * redis://localhost
     *
     * </example>
     *
     * FlashModule constructor.
     * @param string $driver
     */
    public function __construct(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Called just after adding this to a app by calling
     * `$app->addModule(new SomeModule());`
     *
     * Here is the right place to add Routes, etc.
     *
     * @param App $app
     *
     * @return mixed
     */
    public function register(App $app)
    {
        $app->define("flash", function () {
            return new Flash($this->driver);
        });
    }
}
