<?php


use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;

require_once '../vendor/autoload.php';


class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[\Override]
    public function run(): void
    {
        $this->sendTo()->id($this->getId(), 'hello');
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('0.0.0.0',8080);
