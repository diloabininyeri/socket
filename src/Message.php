<?php

namespace Zeus\Pusher;

/**
 *
 */
class Message
{

    /**
     * @param string $message
     * @return string
     */
    public static function encode(string $message): string
    {
//        $message = trim($message);
        $firstByte = 0x81;
        $length = strlen($message);

        if ($length <= 125) {
            $encodedData = chr($firstByte) . chr($length) . $message;
        } elseif ($length <= 65535) {
            $encodedData = chr($firstByte) . chr(126) . pack('n', $length) . $message;
        } else {
            $encodedData = chr($firstByte) . chr(127) . pack('NN', 0, $length) . $message;
        }

        return $encodedData;
    }


    /**
     * @param mixed $message
     * @return string
     */
    public static function decode(mixed $message): string
    {

        $opcode = ord($message[0]) & 0x0F;

        if ($opcode !== 0x01) {
            return ''; // We only support text data, return an empty string if a different opcode arrived.
        }

        $payloadLength = ord($message[1]) & 127;
        if ($payloadLength === 126) {
            $masks = substr($message, 4, 4);
            $payload = substr($message, 8);
        } elseif ($payloadLength === 127) {
            $masks = substr($message, 10, 4);
            $payload = substr($message, 14);
        } else {
            $masks = substr($message, 2, 4);
            $payload = substr($message, 6);
        }

        $decodedData = '';
        for ($i = 0, $strlen = strlen($payload); $i < $strlen; ++$i) {
            $decodedData .= $payload[$i] ^ $masks[$i % 4];
        }

        return $decodedData;
    }
}