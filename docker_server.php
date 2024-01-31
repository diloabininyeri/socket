<?php

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;

require_once 'vendor/autoload.php';


class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[Override]
    public function run(): void
    {
        $this->sendTo()->everyone('everyone');
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve($_ENV['HOST'],$_ENV['PORT']);