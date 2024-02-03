<?php

namespace Zeus\Pusher;

use Zeus\Pusher\Exceptions\ClientException;

/**
 *
 */
class SocketClient
{
    /**
     * @var false|resource
     */
    private $client;

    /**
     * @param string $host
     * @param string $port
     * @param int $timeout
     */
    public function __construct(string $host, string $port, int $timeout = 5)
    {
        $tcpAddress = sprintf('tcp://%s:%d', $host, $port);
        $this->client = stream_socket_client(
            $tcpAddress,
            $errno,
            $errorString,
            $timeout
        );

        if ($errorString) {
            throw new ClientException($errorString);
        }

        register_shutdown_function($this->close(...));
    }


    /**
     * @param string $message
     * @return false|int
     */
    public function send(string $message): false|int
    {
        fwrite($this->client, $message);//@TODO intentionally I wrote this,It needs to be solved later
        return fwrite($this->client, $message);
    }

    /**
     * @param int $seconds
     * @return void
     */
    public function sleep(int $seconds = 1): void
    {
        sleep($seconds);
    }

    /***
     * @param int $length
     * @return false|string
     */
    public function read(int $length = 1024): false|string
    {
        return fread($this->client, $length);
    }

    /**
     * @return void
     */
    public function close(): void
    {
        fclose($this->client);
    }

    /**
     * @return false|resource
     */
    public function getClient(): false
    {
        return $this->client;
    }
}

