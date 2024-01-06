The library continues to be developed...

for install
```console
composer require zeus/pusher:dev-main
```
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
supports the wildcard

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

For example, subscribing to channels with json and sending messages to subscribe channels
js code
```javascript

const message = document.querySelector('#message');
const channel = document.querySelector('#channel');
const data = {channel: channel.value, message: message.value};
socket.send(JSON.stringify(data));

```
php code 
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