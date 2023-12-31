<?php

namespace Zeus\Pusher;

use Socket;

class SocketServer
{
    private Socket $socket;
    /**
     * @var Socket[] $clients
     */
    private array $clients;

    private int $timeout = 5;
    /**
     * @var AbstractSocketClientHandler[] $clientHandlers
     */
    private array $clientHandlers = [];

    private Broadcast $broadcast;

    /**
     * @class-string AbstractSocketClientHandler  $handlerClass
     */
    public function __construct(private readonly string $handlerClass)
    {
        $this->broadcast = new Broadcast();
    }

    public function serve(string $host, int $port): never
    {
        $this->createSocket($host, $port);
        while (true) {
            $this->handleConnections();
            usleep(1000);
        }
    }

    private function handleConnections(): void
    {

        $this->runAllHandlers(); //@TODO this line should be solved
        $this->reset();//@TODO this line should be solved

        $sockets = $this->clients;
        $exceptions = $sockets;
        socket_select($sockets, $wr, $exceptions, $this->getTimeout());
        foreach ($sockets as $socket) {
            if ($socket === $this->socket) {
                $this->clients[] = socket_accept($this->socket);
            } else {
                $this->broadcast->join('public', $socket);
                $handler = $this->getSocketHandlerInstance();
                $this->setSocketOfHandler($handler, $socket, socket_read($socket, 1024));
            }
        }
        usleep(1000);
    }

    private function reset(): void
    {
        $this->broadcast = new Broadcast();
        $this->clientHandlers = [];
    }

    private function runAllHandlers(): void
    {
        foreach ($this->clientHandlers as $handler) {
            $handler->run();
        }
    }

    private function getSocketHandlerInstance(): AbstractSocketClientHandler
    {
        return new ($this->handlerClass)($this->broadcast);
    }

    private function setSocketOfHandler(
        AbstractSocketClientHandler $clientHandler,
        Socket                      $socket,
        string|false                $message): void
    {

        if ($message) {
            $clientHandler->setSocket($socket);
            $clientHandler->setMessage($message);
            $this->addSocketHandlerInstance($clientHandler);
            return;
        }

        $this->removeHandlerInstance($clientHandler);
        $this->removeSocket($socket);

    }

    private function addSocketHandlerInstance(AbstractSocketClientHandler $clientHandler): void
    {
        $this->clientHandlers[] = $clientHandler;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    private function removeFromClients(Socket $socket): void
    {
        $index = array_search($socket, $this->clients, true);
        unset($this->clients[$index]);
    }

    /**
     * @param string $host
     * @param int $port
     * @return void
     */
    private function createSocket(string $host, int $port): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->socket, $host, $port);
        socket_listen($this->socket);
        $this->clients[] = $this->socket;
    }

    private function removeHandlerInstance(AbstractSocketClientHandler $client): void
    {
        foreach ($this->clientHandlers as $key => $handler) {
            if ($client === $handler->getSocket()) {
                unset($this->clientHandlers[$key]);
            }
        }
    }

    /**
     * @param Socket $socket
     * @return void
     */
    private function removeSocket(Socket $socket): void
    {
        $this->removeFromClients($socket);
        $this->broadcast->forget($socket);
        $this->broadcast->close($socket);
    }
}