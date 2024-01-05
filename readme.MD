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
test.html
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Client</title>
</head>
<body>
<button onclick="sendCalculation()">Calculate</button>
<div id="result"></div>

<script>
    const socket = new WebSocket('ws://127.0.0.1:8080');

    socket.addEventListener('open', (event) => {
        console.log('WebSocket connection opened');

        // Her iki saniyede bir mesaj gönder
    });

    socket.addEventListener('message', (event) => {
        const message = event.data;
        alert(`Received message: ${message}`);
    });

    socket.addEventListener('close', (event) => {
        console.log('socket is closed', JSON.stringify(event));
    });

    function sendCalculation() {
        if (socket.readyState === WebSocket.OPEN) {
            socket.send('hello world');
            // Bağlantı tamamlandı, şimdi mesaj gönderebiliriz

        } else {
            // Bağlantı tamamlanmadı, bir hata mesajı gösterebiliriz veya başka bir işlem yapabiliriz
            console.error('Bağlantı hala tamamlanmadı.');
        }
    }

</script>
</body>
</html>
```
