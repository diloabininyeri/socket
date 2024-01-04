<?php


require_once 'vendor/autoload.php';

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\DebuggerInterface;
use Zeus\Pusher\HandShake;
use Zeus\Pusher\Message;
use Zeus\Pusher\SocketServer;


class DebuggerX implements DebuggerInterface
{

    #[\Override]
    public function onRead(string $message): void
    {
//        var_dump($message);

    }
}


class TestHandler extends AbstractSocketClientHandler
{

    /**
     * @throws JsonException
     */
    #[Override]
    public function run(): void
    {
        $this->getMessage();
        $this->broadcast->sendToEveryone('hello world');
    }
}


$socketServer = new SocketServer(TestHandler::class);
$socketServer->serve('127.0.0.1', 8080);
