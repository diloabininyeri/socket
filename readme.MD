The library continues to be developed...
server.php
```php
use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;


class ClientHandler extends AbstractSocketClientHandler
{


    #[\Override]
    public function run(): void
    {
        $this->sendToEveryone($this->getMessage());
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
