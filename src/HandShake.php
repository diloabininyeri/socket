<?php

namespace Zeus\Pusher;

use Socket;


/**
 *
 */
class HandShake
{
    /**
     * @var array
     */
    private array $headers;
    /**
     * @var Socket
     */
    private Socket $socket;


    private array $rawHeaders;

    /**
     * @param array $headers
     * @param Socket $socket
     * @param array $rawHeaders
     */
    public function __construct(array $headers, Socket $socket, array $rawHeaders = array())
    {
        $this->headers = $headers;
        $this->socket = $socket;
        $this->rawHeaders = $rawHeaders;
    }

    /**
     * @param Socket $socket
     * @return self
     */
    public static function to(Socket $socket): self
    {
        [$headers, $rawHeaders] = static::readHeaders($socket);
        return new self($headers, $socket, $rawHeaders);
    }

    /**
     * @return void
     */
    public function accept(): void
    {
        if (!isset($this->headers['Sec-WebSocket-Key'])) {
            return;
        }
        $key = $this->headers['Sec-WebSocket-Key'];
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

        socket_write($this->socket, $response, strlen($response));
    }

    /**
     * @param Socket $socket
     * @return array
     */
    private static function readHeaders(Socket $socket): array
    {
        $headers = [];
        $rawHeaders = preg_split("/\r\n/", socket_read($socket, 4096), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($rawHeaders as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        return [$headers, $rawHeaders];
    }

    public function getPath(): string
    {
        foreach ($this->rawHeaders as $header) {
            if (preg_match('/^GET \/(\S+) HTTP\/(\S+)$/', $header, $matches)) {
                return sprintf('/%s', $matches[1]);
            }
        }
        return '/';
    }
}
