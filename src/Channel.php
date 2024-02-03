<?php

namespace Zeus\Pusher;

use Socket;

/**
 *
 */
class Channel
{

    use MacroTrait;
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
        if (!$this->hasJoin($socket)) {
            $this->sockets[] = $socket;
        }
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
    public function getClients(): array
    {
        return $this->sockets;
    }

    /**
     * @param Socket $socket
     * @return bool
     */
    public function hasJoin(Socket $socket): bool
    {
        return in_array($socket, $this->sockets, true);
    }
}
