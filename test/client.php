<?php


use Zeus\Pusher\SocketClient;

require_once '../vendor/autoload.php';


$socketClient = new SocketClient('0.0.0.0', 8080);

$socketClient->sleep(2);
$socketClient->send('hello world');
echo $socketClient->read();
