<?php


use Zeus\Pusher\SocketClient;




$socketClient = new SocketClient('0.0.0.0', 8080);

$socketClient->sleep(1);
$socketClient->send('hello world');

echo $socketClient->read();
