<?php


$socket_client = stream_socket_client('tcp://localhost:8080');

while (true) {

    $date=date('Y-m-d H:i:s');
    $message = "merhaba $date";
    fwrite($socket_client,$message);
    echo fread($socket_client, 1024) . PHP_EOL . PHP_EOL;
    sleep(1);

}
fclose($socket_client);
