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

    /**
     * @param array $headers
     * @param Socket $socket
     */
    public function __construct(array $headers, Socket $socket)
    {
        $this->headers = $headers;
        $this->socket = $socket;
    }

    /**
     * @param Socket $socket
     * @return self
     */
    public static function to(Socket $socket): self
    {
        return new self(static::readHeaders($socket), $socket);
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
        $lines = preg_split("/\r\n/", socket_read($socket, 4096), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        return $headers;
    }
}
