<?php

namespace Zeus\Pusher;

use Socket;
use Zeus\Pusher\Exceptions\InvalidPatternException;

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
     * @var Send
     */
    private Send $sendInstance;

    /**
     *
     */
    public function __construct()
    {
        $this->channels = [
            'public' => new Channel('public')
        ];
        $this->sendInstance = new Send($this);
    }

    /**
     * @param string $wildcardPattern
     * @return array
     */
    private function getSocketsByPattern(string $wildcardPattern): array
    {
        $clients = [];
        foreach ($this->getChannelNamesByWildcard($wildcardPattern) as $channelName) {
            $clients[] = $this->findChannel($channelName)->getClients();
        }

        return Arr::unique(
            Arr::flatten($clients)
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
     * @param Socket $client
     * @return void
     */
    public function join(string $channelName, Socket $client): void
    {
        $channel = $this->channels[$channelName] ?? new Channel($channelName);
        $channel->join($client);
        $this->channels[$channelName] = $channel;
    }

    /**
     * @param string $path
     * @param Socket $client
     * @return void
     */
    public function joinRoute(string $path, Socket $client): void
    {
        $channelName = $this->createChannelNameByRoute($path);
        $this->join($channelName, $client);
    }

    /**
     * @param string $channelName
     * @param Socket $client
     * @return bool
     */
    public function hasJoin(string $channelName, Socket $client): bool
    {
        if (!$this->hasChannel($channelName)) {
            return false;
        }
        return $this->findChannel($channelName)->hasJoin($client);
    }

    /**
     * @param string $channel
     * @param Socket $client
     * @return void
     */
    public function leave(string $channel, Socket $client): void
    {
        if (isset($this->channels[$channel])) {
            $this->channels[$channel]->leave($client);
        }
    }

    /**
     * @param Socket $socket
     * @return void
     */
    public function forget(Socket $socket): void
    {
        foreach ($this->channels as $channel) {
            foreach ($channel->getClients() as $client) {
                if ($client === $socket) {
                    $channelName = $channel->getName();
                    $this->leave($channelName, $socket);
                    break 2;
                }
            }
        }
    }

    /**
     * @param Socket $client
     * @return void
     */
    public function close(Socket $client): void
    {
        foreach ($this->channels as $channel) {
            $channel->leave($client);
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
        return $this->channels[$channelName] ??= new Channel($channelName);
    }

    /***
     * @return Send
     */
    public function sendTo(): Send
    {
        return $this->sendInstance;
    }
    /**
     * @param Socket $client
     * @return void
     */
    public function disconnect(Socket $client): void
    {
        $this->forget($client);
        $this->close($client);
        socket_shutdown($client);
    }

    /**
     * @param string $channelName
     * @return array
     */
    public function getSockets(string $channelName): array
    {
        if (str_contains($channelName, '.*')) {
            return $this->getSocketsByPattern($channelName);
        }

        return $this->channels[$channelName]?->getClients() ?? [];
    }

    /**
     * @param string $path
     * @return string
     */
    public function createChannelNameByRoute(string $path): string
    {
        $trim = trim($path, '/');
        if (empty($trim)) {
            $trim = '/';
        }
        return sprintf('route_path_%s', $trim);
    }
}
