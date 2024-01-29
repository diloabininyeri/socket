<?php

namespace Zeus\Pusher;

use Socket;

/**
 *
 */
class Id
{

    /**
     * @param Socket $socket
     * @return string
     */
    public static function get(Socket $socket):string
    {
        return md5(spl_object_id($socket));
    }

    /**
     * @param string $id
     * @param Socket $socket
     * @return bool
     */
    public static function isBelong(string $id, Socket $socket):bool
    {
        return static::get($socket) === $id;
    }
}
