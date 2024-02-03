<?php

namespace Zeus\Pusher;

use Socket;
use Zeus\Pusher\exceptions\SocketException;

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
            $this->runAllHandlers();
            $this->handleConnections();
            usleep(1000);
        }
    }

    /**
     * @return void
     */
    private function handleConnections(): void
    {
        $clients = $this->clients;
        $socketSelect = socket_select($clients, $wr, $exc, $this->getTimeout());

        if ($socketSelect === false) {
            $this->handleSocketError();
        }
        foreach ($clients as $client) {
            if ($client === $this->socket) {
                $this->handleNewConnection();
            } else {
                $this->handleExistingConnection($client);
            }
        }
    }

    /**
     * @return void
     */
    private function runAllHandlers(): void
    {
        foreach ($this->clientHandlers as $key => $clientHandler) {
            $clientHandler->run();
            unset($this->clientHandlers[$key]);
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
     * @param Socket $client
     * @param string|false $message
     * @return void
     */
    private function setSocketOfHandler(
        AbstractSocketClientHandler $clientHandler,
        Socket                      $client,
        string|false                $message): void
    {

        if ($message) {
            $clientHandler->setClient($client);
            $clientHandler->setMessage($message);
            $this->addSocketHandlerInstance($clientHandler);
            return;
        }

        $this->removeSocket($client);
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
     * @param Socket $client
     * @return void
     */
    private function removeFromClients(Socket $client): void
    {
        $index = array_search($client, $this->clients, true);
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
        $socketBind = socket_bind($this->socket, $host, $port);
        if ($socketBind === false) {
            $this->throwException();
        }
        $socketListen = socket_listen($this->socket);
        if ($socketListen === false) {
            $this->throwException();
        }
        $this->clients[] = $this->socket;
    }

    /**
     * @param AbstractSocketClientHandler $client
     * @return void
     */
    private function removeHandlerInstance(AbstractSocketClientHandler $client): void
    {

        foreach ($this->clientHandlers as $key => $handler) {
            if ($client === $handler->getClient()) {
                unset($this->clientHandlers[$key]);
            }
        }
    }

    /**
     * @param Socket $client
     * @return void
     */
    private function removeSocket(Socket $client): void
    {
        $this->removeFromClients($client);
        $this->broadcast->forget($client);
        $this->broadcast->close($client);
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

    /**
     * @return void
     */
    private function handleNewConnection(): void
    {
        $newClient = socket_accept($this->socket);
        if (false !== $newClient) {
            $this->clients[] = $newClient;
            $handShake = HandShake::to($newClient);
            $handShake->accept();
            if ($handShake->getPath() !== '/') {
                $this->broadcast->joinRoute($handShake->getPath(), $newClient);
            }
        }
    }

    /**
     * @param Socket $client
     * @return void
     */
    private function handleExistingConnection(Socket $client): void
    {
        $this->broadcast->join('public', $client);

        $this->setSocketOfHandler(
            $this->getSocketHandlerInstance(),
            $client,
            $read = socket_read($client, 1024)
        );
        $this->callDebug($read);
    }

    /**
     * @return void
     */
    private function handleSocketError(): void
    {
        $errorCode = socket_last_error();
        $errorMessage = socket_strerror($errorCode);
        echo "Error reading from socket: [$errorCode] $errorMessage\n";
    }

    /***
     * @return void
     */
    private function throwException(): void
    {
        throw new SocketException(
            socket_strerror(
                socket_last_error($this->socket)
            )
        );
    }

    /***
     * @param false|string $read
     * @return void
     */
    private function callDebug(false|string $read): void
    {
        if (Message::isEncoded($read)) {
            $this->debugger->onRead(Message::decode($read));
            return;
        }
        $this->debugger->onRead($read);
    }
}
