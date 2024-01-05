<?php

namespace Zeus\Pusher;

use Override;

class Debugger implements DebuggerInterface
{

    #[Override]
    public function onRead(string $message): void
    {
        $date = date('Y-m-d H:i:s');
        echo "\e[32m $date: $message\e[0m";
        echo PHP_EOL;
    }
}
