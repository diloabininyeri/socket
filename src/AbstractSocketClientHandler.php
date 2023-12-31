<?php

namespace Zeus\Pusher;

use Socket;


/**
 *
 */
abstract class AbstractSocketClientHandler
{
    /**
     * @var Socket
     */
    private Socket $socket;

    private string|false $message;


    public function __construct(protected readonly Broadcast $broadcast)
    {
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function setSocket(Socket $socket): void
    {
        $this->socket = $socket;
    }

    /**
     * @return Socket
     */
    public function getSocket(): Socket
    {
        return $this->socket;
    }

    /***
     * @return string|false
     */
    public function getMessage(): string|false
    {
        return $this->message;
    }

    public function setMessage(false|string $message): void
    {
        $this->message = $message;
    }

    protected function join(string $channel): void
    {
        $this->broadcast->join($channel, $this->socket);
    }

    /**
     * @return void
     */
    abstract public function run(): void;
}