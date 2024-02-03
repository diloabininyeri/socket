<?php

namespace Zeus\Pusher;

use Closure;

/**
 *
 */
trait MacroTrait
{
    /**
     * @var array
     */
    private static array $macros = [];

    /**
     * @param string $method
     * @param Closure $macro
     * @return void
     */
    public static function method(string $method, Closure $macro): void
    {
        static::$macros[$method] ??= $macro;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return static::$macros[$method]->call($this, ...$arguments);
    }
}
