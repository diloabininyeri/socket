<?php

namespace Zeus\Pusher;

use Socket;

/**
 *
 */
readonly class HandShake
{


    /**
     * @param array $headers
     * @param Socket $socket
     */
    public function __construct(private array $headers, private Socket $socket)
    {
    }

    /**
     * @param Socket $socket
     * @return self
     */
    public static function to(Socket $socket): self
    {
        $headers = [];
        $lines = preg_split("/\r\n/", socket_read($socket, 4096), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        return new self($headers, $socket);
    }

    /**
     * @return void
     */
    public function accept(): void
    {
        $key = $this->headers['Sec-WebSocket-Key'];
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

        socket_write($this->socket, $response, strlen($response));

    }
}