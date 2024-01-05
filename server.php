<?php


require_once 'vendor/autoload.php';

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\DebuggerInterface;
use Zeus\Pusher\SocketServer;


class DebuggerX implements DebuggerInterface
{

    #[\Override]
    public function onRead(string $message): void
    {
//        var_dump($message);

    }
}


class ClientHandler extends AbstractSocketClientHandler
{


    #[\Override]
    public function run(): void
    {
        $this->sendToEveryone('hello world');
    }
}


$socketServer = new SocketServer(ClientHandler::class);
$socketServer->serve('127.0.0.1', 8080);
