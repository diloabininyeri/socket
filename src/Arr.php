<?php

namespace Zeus\Pusher;

/**
 *
 */
class Arr
{
    /**
     * @param array $array
     * @return array
     */
    public static function flatten(array $array): array
    {
        return array_reduce($array, static function ($acc, $item) {
            return array_merge($acc, is_array($item) ? self::flatten($item) : [$item]);
        }, []);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function unique(array $array): array
    {
        return array_unique($array, SORT_REGULAR);
    }

    /**
     * @param string $dotNotation
     * @param array $array
     * @param mixed $default
     * @return mixed
     */
    public static function dot(string $dotNotation, array $array, mixed $default=null): mixed
    {
        $keys = explode('.', $dotNotation);
        foreach ($keys as $nestedKey) {
            if (isset($array[$nestedKey])) {
                $array = $array[$nestedKey];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
