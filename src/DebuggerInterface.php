<?php

namespace Zeus\Pusher;

/**
 *
 */
interface DebuggerInterface
{

    /**
     * @param string $message
     * @return void
     */
    public function onRead(string $message): void;
}