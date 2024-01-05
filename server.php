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
        $this->broadcast->sendToEveryone('hello');

        $channel = $this
            ->broadcast
            ->createAndJoin('notification', $this->getClient());


        $this->broadcast->send($channel,$this->getMessage());

        $this->broadcast->hasChannel('test_channel');

        $this->broadcast->createChannel('test_channel');


        $this->broadcast->hasJoin('test_channel', $this->getClient());

        $this->broadcast->sendTo('newsletter', 'a blog posted');


        $this->broadcast->sendToEveryone('bye bye');

        $this->broadcast->disconnect(
            $this->getClient()
        );

    }
}


$socketServer = new SocketServer(ClientHandler::class);
$socketServer->serve('127.0.0.1', 8080);
