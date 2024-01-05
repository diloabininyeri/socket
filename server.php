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
        $this->sendToEveryone('hello');

        $channel = $this
            ->createAndJoin('notification', $this->getClient());


        $this->send($channel,$this->getMessage());

        $this->hasChannel('test_channel');

        $this->createChannel('test_channel');


        $this->hasJoin('test_channel', $this->getClient());
        $this->sendTo('newsletter', 'a blog posted');
        $this->sendToEveryone('bye bye');

        $this->disconnect(
            $this->getClient()
        );

    }
}


$socketServer = new SocketServer(ClientHandler::class);
$socketServer->serve('127.0.0.1', 8080);
