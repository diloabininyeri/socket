<?php

namespace Zeus\Pusher;

use JsonException;
use Socket;


/**
 * @mixin Broadcast
 */
abstract class AbstractSocketClientHandler
{
    /**
     * @var Socket
     */
    private Socket $client;

    /**
     * @var string|false
     */
    private string|false $message;


    /**
     * @var array
     */
    private array $extractedJson = [];

    /**
     * @param Broadcast $broadcast
     */
    public function __construct(protected readonly Broadcast $broadcast)
    {
    }

    /**
     * @return void
     */
    abstract public function run(): void;

    /**
     * @param Socket $socket
     * @return void
     */
    public function setClient(Socket $socket): void
    {
        $this->client = $socket;
    }

    /**
     * @return Socket
     */
    public function getClient(): Socket
    {
        return $this->client;
    }

    /***
     * @return string|false
     */
    public function getMessage(): string|false
    {
        if ($this->isFromWebsocket()) {
            return Message::decode($this->message);
        }
        return $this->message;
    }

    /**
     * @return string
     */
    public function getRawMessage(): string
    {
        return $this->message;
    }

    /**
     * @param false|string $message
     * @return void
     */
    public function setMessage(false|string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->broadcast->$method(...$arguments);
    }

    /**
     * @return bool
     */
    public function isMessageJson(): bool
    {
        return json_validate($this->getMessage());
    }

    /**
     * check the message whether it comes from the browser or not
     * @return bool
     */
    public function isFromWebsocket(): bool
    {
        return Message::isEncoded($this->message);
    }

    /**
     * @throws JsonException
     */
    public function getJsonValue(string $dotNotation, mixed $default = null): mixed
    {
        return Arr::dot($dotNotation, $this->getJson(), $default);
    }

    /***
     * @return array
     * @throws JsonException
     */
    public function getJson(): array
    {
        if (!$this->isMessageJson()) {
            return [];
        }
        if ($this->extractedJson) {
            return $this->extractedJson;
        }
        $this->extractedJson = json_decode($this->getMessage(), true, 512, JSON_THROW_ON_ERROR);
        return $this->extractedJson;
    }

    /**
     * @return string
     */
    public function getId():string
    {
        return Id::get($this->getClient());
    }
    /***
     * @return string
     */
    public function getRemoteAddress(): string
    {
        socket_getpeername($this->client, $address, $port);
        return "$address:$port";
    }

    /**
     * @return string
     */
    public function getRemoteHost(): string
    {
        return explode(':', $this->getRemoteAddress())[0];
    }
    /**
     * @param Socket|null $socket
     * @return array
     */
    public function getStatus(Socket $socket=null):array
    {
        return stream_get_meta_data(
            socket_export_stream($socket ?: $this->client)
        );
    }
}
