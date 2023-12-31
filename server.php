<?php


require_once 'vendor/autoload.php';

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;


class TestHandler extends AbstractSocketClientHandler
{

    /**
     */
    #[Override]
    public function run(): void
    {
        $broadcast = $this->broadcast;

        $channelName = 'accountant';

        if (!$broadcast->hasChannel($channelName)) {
            $broadcast->createChannel($channelName);
        }

        $this->broadcast->send(
            $broadcast->getChannel($channelName),
            'a new account has been  registered'
        );

        $broadcast->sendToEveryone($this->getMessage());
    }
}


$socketServer = new SocketServer(TestHandler::class);
$socketServer->setTimeout(5);
$socketServer->serve('127.0.0.1', 8080);
