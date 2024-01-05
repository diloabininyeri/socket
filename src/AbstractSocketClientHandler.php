<?php

namespace Zeus\Pusher;

use Socket;


/**
 * @mixin Broadcast
 */
abstract class AbstractSocketClientHandler
{
    /**
     * @var Socket
     */
    private Socket $socket;

    /**
     * @var string|false
     */
    private string|false $message;


    /**
     * @param Broadcast $broadcast
     */
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
    public function getClient(): Socket
    {
        return $this->socket;
    }

    /***
     * @return string|false
     */
    public function getMessage(): string|false
    {
        return Message::decode($this->message);
    }

    /**
     * @return string
     */
    public function getRawMessage(): string
    {
        return $this->message;
    }

    /**
     * @param false|string $message
     * @return void
     */
    public function setMessage(false|string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param string $channel
     * @return void
     */
    protected function join(string $channel): void
    {
        $this->broadcast->join($channel, $this->socket);
    }


    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->broadcast->$method(...$arguments);
    }

    /**
     * @return void
     */
    abstract public function run(): void;
}