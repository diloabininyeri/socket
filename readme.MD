
### Socket Channels

This is a socket library, it is an advanced library, the purpose of which is to subscribe to channels on the socket and send messages to channels, as well as leaving channels...

for install
```console
composer require zeus/pusher:dev-main
```

### Socket Server Management
We manage the entire socket server with an object inherited from the AbstractSocketClientHandler object...
In the example below, it is the ClientHandler object, but this is entirely up to your wishes.
Let's, For example, let's send the message to everyone.

server.php
```php
use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\Send;use Zeus\Pusher\SocketServer;


class ClientHandler extends AbstractSocketClientHandler
{


    #[\Override]
    public function run(): void
    {
        $this->sendTo()->everyone($this->getMessage());
    }
}


$socketServer = new SocketServer(ClientHandler::class);
$socketServer->serve('127.0.0.1', 8080);

```
Run in the Terminal
```console
php server.php
```
js websocket code

```html

<script>
    const socket = new WebSocket('ws://127.0.0.1:8080');

    socket.addEventListener('open', (event) => {
        console.log('WebSocket connection opened');
        socket.send('test message');
    });

    socket.addEventListener('message', (event) => {
        const message = event.data;
        alert(`Received message: ${message}`);
    });

    socket.addEventListener('close', (event) => {
        console.log('socket is closed', JSON.stringify(event));
    });


</script>
</body>
</html>
```

### Works with the wildcard

You can use wildcard in channel management.

```php
class Handler extends AbstractSocketClientHandler
{

    #[\Override]
    public function run(): void
    {

        $this->join('it.frontend', $this->getClient());
        $this->join('it.backend', $this->getClient());

        $this->sendTo()->channel('it.*', 'hello world');


    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('127.0.0.1', 8080);
```

### Works with the json
Let's subscribe to the channels with the json coming from the frontend and send messages to the channels.
js code

This example subscribes to the channel according to the json data coming from the clients
and sends the message to the subscribed channel.

**In the frontend**
```javascript

const message = document.querySelector('#message');
const channel = document.querySelector('#channel');
const data = {channel: channel.value, message: message.value};
socket.send(JSON.stringify(data));

```
**In the Backend**
```php

class Handler extends AbstractSocketClientHandler
{

    /**
     * @throws JsonException
     */
    #[\Override]
    public function run(): void
    {
        if ($this->isMessageJson()) {

            $this->join(
                $this->getJsonValue('channel'),
                $this->getClient()
            );

            $this->sendTo()->channel(
                $this->getJsonValue('channel'),
                $this->getJsonValue('message')
            );
        }
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('127.0.0.1', 9999);
```

### The client

The php client does not listen to the socket, so what is the purpose?

It allows sending messages to connected connections.
You can even make this client compatible with the API and send messages to language-independent sockets.

This code forwards the message to the connected server and closes the connection.
In this way, it provides incredible flexibility,
you can even create an API socket server, yes, it sounds nice, doesn't it?

```php

use Zeus\Pusher\SocketClient;




$socketClient = new SocketClient('0.0.0.0', 8080);

$socketClient->sleep(1);

$message=json_encode(['channel'=>'backend','message'=>'a new backend developer has applied']);
$socketClient->send($message);

return $socketClient->read();
```

### For more information
to be continued...
<br>
[|||||||||||||||]