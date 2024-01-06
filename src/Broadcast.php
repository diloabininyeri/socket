<?php

namespace Zeus\Pusher;

use Socket;
use Zeus\Pusher\exceptions\InvalidPatternException;

/**
 *
 */
class Broadcast
{
    /**
     * @var Channel[] array of channels
     */
    private array $channels;

    /**
     *
     */
    public function __construct()
    {
        $this->channels = [
            'private' => new Channel('private'),
            'public' => new Channel('public')
        ];
    }

    /**
     * @param string $channelName
     * @param string $message
     * @return void
     */
    public function sendTo(string $channelName, string $message): void
    {
        $sockets = $this->getSockets($channelName);

        if (empty($sockets)) {
            return;
        }
        $exceptions = null;
        $writeSockets = $sockets;
        $select = socket_Select($sockets, $writeSockets, $exceptions, 5);
        if ($select > 0) {
            foreach ($writeSockets as $writeSocket) {
                socket_write($writeSocket, Message::encode($message));
            }
        }
    }

    /**
     * @param string $wildcardPattern
     * @return array
     */
    private function getSocketsByPattern(string $wildcardPattern): array
    {
        $sockets = [];
        foreach ($this->getChannelNamesByWildcard($wildcardPattern) as $channelName) {
            $sockets[] = $this->findChannel($channelName)->getSockets();
        }

        return Arr::unique(
            Arr::flatten($sockets)
        );
    }

    /**
     * @param string $wildcardPattern
     * @return array
     */
    public function getChannelNamesByWildcard(string $wildcardPattern): array
    {

        if (!str_contains($wildcardPattern, '.*')) {
            throw new InvalidPatternException('Wildcard pattern is not a valid wildcard pattern');
        }

        $escaped = str_replace('.*', '\..*', $wildcardPattern);
        $pattern = sprintf('/^%s$/', $escaped);
        $names = [];
        foreach ($this->channels as $channel) {

            if (preg_match($pattern, $channel->getName())) {
                $names[] = $channel->getName();
            }
        }
        return $names;
    }


    /**
     * @param string $channelName
     * @param Socket $socket
     * @return void
     */
    public function join(string $channelName, Socket $socket): void
    {
        $channel = $this->channels[$channelName] ?? new Channel($channelName);
        $channel->join($socket);
        $this->channels[$channelName] = $channel;
    }

    /**
     * @param string $channelName
     * @param Socket $socket
     * @return bool
     */
    public function hasJoin(string $channelName, Socket $socket): bool
    {
        if (!$this->hasChannel($channelName)) {
            return false;
        }
        return $this->findChannel($channelName)->hasSocket($socket);
    }

    /**
     * @param string $channel
     * @param Socket $socket
     * @return void
     */
    public function leave(string $channel, Socket $socket): void
    {
        if (isset($this->channels[$channel])) {
            $this->channels[$channel]->leave($socket);
        }
    }

    /**
     * @param string $message
     * @return void
     */
    public function sendToEveryone(string $message): void
    {
        foreach ($this->channels as $channel) {
            $this->sendTo($channel->getName(), $message);
        }
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function forget(Socket $socket): void
    {
        foreach ($this->channels as $channel) {
            foreach ($channel->getSockets() as $client) {
                if ($client === $socket) {
                    $channelName = $channel->getName();
                    $this->leave($channelName, $socket);
                    break 2;
                }
            }
        }
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function close(Socket $socket): void
    {
        foreach ($this->channels as $channel) {
            $channel->leave($socket);
        }
    }

    /**
     * @param string $channelName
     * @return bool
     */
    public function hasChannel(string $channelName): bool
    {
        return isset($this->channels[$channelName]);
    }

    /**
     * @param string $channel
     * @return Channel
     */
    public function findChannel(string $channel): Channel
    {
        return $this->channels[$channel];
    }

    /**
     * @return Channel
     */
    public function getPublicChannel(): Channel
    {
        return $this->channels['public'];
    }

    /**
     * @return Channel[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @param string $channelName
     * @return Channel
     */
    public function createChannel(string $channelName): Channel
    {
        return $this->channels[$channelName] = new Channel($channelName);
    }

    /**
     * @param Channel $channel
     * @param string $message
     * @return void
     */
    public function send(Channel $channel, string $message): void
    {
        $this->sendTo($channel->getName(), $message);
    }

    /**
     * @param string $channelName
     * @param string $message
     * @return void
     */
    public function sendToExcept(string $channelName, string $message): void
    {
        foreach ($this->getChannels() as $channel) {
            if ($channel->getName() !== $channelName) {
                $this->sendTo($channel->getName(), $message);
            }
        }

    }

    /**
     * @param string $channelName
     * @param Socket $socket
     * @return Channel
     */
    public function createAndJoin(string $channelName, Socket $socket): Channel
    {
        if (!$this->hasChannel($channelName)) {
            $this->createChannel($channelName);
        }
        if (!$this->hasJoin($channelName, $socket)) {
            $this->join($channelName, $socket);
        }
        return $this->channels[$channelName];
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function disconnect(Socket $socket): void
    {
        $this->forget($socket);
        $this->close($socket);
        socket_shutdown($socket);
    }

    /**
     * @param string $channelName
     * @return array
     */
    private function getSockets(string $channelName): array
    {
        if (str_contains($channelName, '.*')) {
            return $this->getSocketsByPattern($channelName);
        }

        return $this->channels[$channelName]?->getSockets() ?? [];
    }
}
