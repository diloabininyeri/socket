<?php

namespace Zeus\Pusher;

use Socket;

/**
 *
 */
readonly class Send
{

    /**
     * @param Broadcast $broadcast
     */
    public function __construct(private Broadcast $broadcast)
    {
    }

    /**
     * @param string $message
     * @return void
     */
    public function everyone(string $message): void
    {
        $this->channel('public', $message);
    }

    /***
     * @param string $channelName
     * @param string $message
     * @return void
     */
    public function channel(string $channelName, string $message): void
    {
        $sockets = $this->broadcast->getSockets($channelName);

        if (empty($sockets)) {
            return;
        }
        $exceptions = null;
        $writeSockets = $sockets;
        $select = socket_select($sockets, $writeSockets, $exceptions, 5);
        if ($select > 0) {
            foreach ($writeSockets as $writeSocket) {
                socket_write($writeSocket, Message::encode($message));
            }
        }
    }

    /***
     * @param string $channelName
     * @param string $message
     * @return void
     */
    public function except(string $channelName, string $message): void
    {
        foreach ($this->broadcast->getChannels() as $channel) {
            if ($channel->getName() !== $channelName) {
                $this->channel($channel->getName(), $message);
            }
        }

    }
    /**
     * @param Socket $client
     * @param string $message
     * @return void
     */
    public function client(Socket $client, string $message): void
    {
        socket_write($client, Message::encode($message));
    }
    /**
     * @param array $channelNames
     * @param string $message
     * @return void
     */
    public function channels(array $channelNames, string $message): void
    {
        foreach ($channelNames as $channelName) {
            $this->channel($channelName, $message);
        }
    }


    /**
     * @param Socket $client
     * @param string $message
     * @return void
     */
    public function exceptClient(Socket $client, string $message): void
    {
        $channel = $this->broadcast->findChannel('public');
        $sockets=$channel->getSockets();

        if (empty($sockets)) {
            return;
        }
        $exceptions = null;
        $writeSockets = $sockets;
        $select = socket_select($sockets, $writeSockets, $exceptions, 5);
        if ($select > 0) {
            foreach ($writeSockets as $writeSocket) {
                if ($client === $writeSocket) {
                    continue;
                }
                socket_write($writeSocket, Message::encode($message));
            }
        }
    }
}
