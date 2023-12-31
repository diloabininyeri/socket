<?php

namespace Zeus\Pusher;

use Socket;

/**
 *
 */
class Channel
{
    /**
     * @var array
     */
    private array $sockets = [];

    /**
     * @param string $name
     */
    public function __construct(private readonly string $name)
    {
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function join(Socket $socket): void
    {
        $this->sockets[] = $socket;
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function leave(Socket $socket): void
    {
        $index = array_search($socket, $this->sockets, true);
        if ($index !== false) {
            unset($this->sockets[$index]);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSockets(): array
    {
        return $this->sockets;
    }
}
