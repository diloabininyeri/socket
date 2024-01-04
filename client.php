<?php


$socket_client = stream_socket_client('tcp://localhost:8080');


$channel = $argv[1];
$data = [
    'channel' => $channel,
    'data'=>"hello from $channel ".date('Y-m-d H:i:s')
];


while (true) {
    
    
    $message =$data;
    fwrite($socket_client,json_encode($message));
    echo fread($socket_client, 1024) . PHP_EOL . PHP_EOL;
    sleep(1);

}
fclose($socket_client);


