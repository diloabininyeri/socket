<?php

use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;

require_once 'vendor/autoload.php';


class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[Override]
    public function run(): void
    {

        $this->hasRoute('/chat');//Is there any client connecting via /chat route?
        $this->isRoute('/chat'); //Is the current client connected via the /chat route?

        $this->sendTo()->everyone('hello wo');
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve(
    $_ENV['HOST'] ?? '0.0.0.0',
    $_ENV['PORT'] ?? 8080
);
