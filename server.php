<?php


require_once 'vendor/autoload.php';

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;
use Zeus\Pusher\DebuggerInterface;


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

        $json_decode = json_decode($this->getMessage(), true, 512, JSON_THROW_ON_ERROR);
        ['channel' => $channel, 'data' => $data] = $json_decode;

        $this->broadcast->createAndJoin($channel, $this->getSocket());

        $this->broadcast->sendTo($channel, $data);

    }
}


$socketServer = new SocketServer(TestHandler::class);
$socketServer->serve('127.0.0.1', 8080);
