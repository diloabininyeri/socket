<?php

namespace Zeus\Pusher;

class Debugger implements DebuggerInterface
{

    #[\Override]
    public function onRead(string $message): void
    {
        echo date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
    }
}