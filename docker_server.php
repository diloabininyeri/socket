<?php

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\Message;
use Zeus\Pusher\Send;
use Zeus\Pusher\SocketServer;

require_once 'vendor/autoload.php';


class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[Override]
    public function run(): void
    {
        Send::method(
            'test',
            static fn(Socket $client) => socket_write($client, Message::encode('test'))
        );


        $this->sendTo()->test($this->getClient());
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve(
    $_ENV['HOST'] ?? '0.0.0.0',
    $_ENV['PORT'] ?? 8080
);
