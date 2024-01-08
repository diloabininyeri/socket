<?php


use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;

require_once '../vendor/autoload.php';


class Handler extends AbstractSocketClientHandler
{

    /**
     * @throws JsonException
     */
    #[\Override]
    public function run(): void
    {
        $this->sendTo()->everyone(
            $this->getJsonValue('message','default message')
        );
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('0.0.0.0',8080);
