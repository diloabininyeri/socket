<?php

namespace Zeus\Pusher;

use Socket;

/**
 *
 */
class SocketServer
{
    /**
     * @var Socket
     */
    private Socket $socket;
    /**
     * @var Socket[] $clients
     */
    private array $clients;

    /**
     * @var int
     */
    private int $timeout = 5;
    /**
     * @var AbstractSocketClientHandler[] $clientHandlers
     */
    private array $clientHandlers = [];

    /**
     * @var Broadcast
     */
    private Broadcast $broadcast;


    /**
     * @var DebuggerInterface $debugger
     */
    private DebuggerInterface $debugger;


    /**
     * @class-string AbstractSocketClientHandler  $handlerClass
     */
    public function __construct(private readonly string $handlerClass)
    {
        $this->broadcast = new Broadcast();
        $this->debugger = new Debugger();
    }

    /**
     * @param string $host
     * @param int $port
     * @return never
     */
    public function serve(string $host, int $port): never
    {
        $this->createSocket($host, $port);
        printf('socket started at ws://%s:%d %s', $host, $port, PHP_EOL);
        while (true) {
            $this->handleConnections();
            usleep(1000);
        }
    }

    /**
     * @return void
     */
    private function handleConnections(): void
    {


        $this->runAllHandlers(); //@TODO this line should be solved
        $this->reset();//@TODO this line should be solved

        $sockets = $this->clients;
        $exceptions = $sockets;
        $socketSelect = socket_select($sockets, $wr, $exceptions, $this->getTimeout());

        if ($socketSelect === false) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            echo "Error reading from socket: [$errorCode] $errorMessage\n";
        }
        foreach ($sockets as $socket) {
            if ($socket === $this->socket) {
                $this->clients[] = socket_accept($this->socket);
            } else {
                $this->broadcast->join('public', $socket);

                $this->setSocketOfHandler(
                    $this->getSocketHandlerInstance(),
                    $socket,
                    $read = socket_read($socket, 1024)
                );
                $this->debugger->onRead($read);
            }
        }

        usleep(1000);
    }

    /**
     * @return void
     */
    private function reset(): void
    {
        $this->broadcast = new Broadcast();
        $this->clientHandlers = [];
    }

    /**
     * @return void
     */
    private function runAllHandlers(): void
    {
        foreach ($this->clientHandlers as $handler) {
            $handler->run();
        }
    }

    /**
     * @return AbstractSocketClientHandler
     */
    private function getSocketHandlerInstance(): AbstractSocketClientHandler
    {
        return new ($this->handlerClass)($this->broadcast);
    }

    /**
     * @param AbstractSocketClientHandler $clientHandler
     * @param Socket $socket
     * @param string|false $message
     * @return void
     */
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

        $this->removeSocket($socket);
        $this->removeHandlerInstance($clientHandler);

    }

    /**
     * @param AbstractSocketClientHandler $clientHandler
     * @return void
     */
    private function addSocketHandlerInstance(AbstractSocketClientHandler $clientHandler): void
    {
        $this->clientHandlers[] = $clientHandler;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return void
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @param Socket $socket
     * @return void
     */
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

    /**
     * @param AbstractSocketClientHandler $client
     * @return void
     */
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

    /**
     * @param DebuggerInterface $debugger
     * @return $this
     */
    public function setDebugger(DebuggerInterface $debugger): SocketServer
    {
        $this->debugger = $debugger;
        return $this;
    }
}