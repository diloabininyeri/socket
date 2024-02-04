
### Socket Channels

This is a socket library, it is an advanced library, the purpose of which is to subscribe to channels on the socket and send messages to channels, as well as leaving channels...

for install
```console
composer require zeus/pusher
```

### Socket Server Management
We manage the entire socket server with an object inherited from the AbstractSocketClientHandler object...
In the example below, it is the ClientHandler object, but this is entirely up to your wishes.
Let's, For example, let's send the message to everyone.



server.php
```php
use Zeus\Pusher\AbstractSocketClientHandler;
use Zeus\Pusher\SocketServer;


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
Here, the join method is subscribing to the channel, at the same time, you can use the leave method to cancel the subscription to the channel, you can subscribe to the channels and receive notifications, in fact, the main logic is like a real scenario and if we want to give up notifications to the channels, we use the leave method, that's all.

Additionally, there are more methods besides methods such as hasJoin, 
join and leave,this is purely for managing channels well...

In the example below, we subscribe to `it.backend` and `it.frontend` channels and the beginning is `it.` We send notifications to all channels that start,
the purpose here is to group the channels.

First of all, we use the `$this->getClient()` method to get the connected client...

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

### Clients of a channel
>You can get all the clients subscribed to a channel for example by default, all clients are subscribed to the channel named public.

>You can receive all channels in the system and attract clients connected to these channels and perform transactions.
```php

class Handler extends AbstractSocketClientHandler
{

    #[\Override]
    public function run(): void
    {
      //get all channels
       $channels=$this->getChannels();
       
       //get all clients of the public channel
       $clients=$this->findChannel('public')->getClients();
       foreach($clients as $client){
           $ip=$this->getRemoteHost($client);
           if('x.x.x.x'===$ip){
               $this->disconnect($client);
           }
       }
       
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('127.0.0.1', 8080);
```
**Some methods for the channel, but don't forget there are more**

```php

class Handler extends AbstractSocketClientHandler
{

    #[\Override]
    public function run(): void
    {
        $this->leave('it.backend', $this->getClient());
        $this->hasJoin('it.backend', $this->getClient());
        $this->hasChannel('it.backend');
        $this->findChannel('it.backend');
        $this->getChannels();
        //and more
    }
}
```

> note: leave method does not support wildcard for now.
> it will not working,I thought this was not appropriate due to its completely irreversible effects
```php
$this->leave('it.*');
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

/**
 * This is a PHP code that extends
 * the AbstractSocketClientHandler class
 * and implements the run() method.
 * It checks if the received message is in JSON format,
 * joins the specified channel, and sends the message to that channel.
 */

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
$socketServer->serve('127.0.0.1', 8080);
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
                                                                  
$message=json_encode([
    'channel'=>'backend',
    'message'=>'a new backend developer has applied'
    ]);    
         
$socketClient->send($message);                                    
                                                                  
return $socketClient->read();                                     
```
### Send a custom message
You can send a message to a special client, for example, let's send a message only to the currently connected client.
```php
class Handler extends AbstractSocketClientHandler
{

    #[\Override]
    public function run(): void
    {

        //send yourself a message
        $this->sendTo()->client($this->getClient(),'Hello, world');
        //send it to everyone except himself
        $this->sendTo()->exceptClient($this->getClient(),'Hello world');
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('127.0.0.1', 8080);
```

### Send a message by socket id
The socket ID can be used to send a custom message. Below is an example of sending a message to a specific socket ID.
A feature designed especially for sending personal messages.
```php
class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[\Override]
    public function run(): void
    {
        $this->sendTo()->id($this->getId(), 'hello');
    }
}
```

### Websocket routes
You can create a namespace using js websocket and route path, below are examples of js and php codes.

```php

class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[\Override]
    public function run(): void
    {
        $this->sendTo()->route('/chat', 'hello world');
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve('0.0.0.0',8080);
```
javascript websocket 

```js
const socket = new WebSocket('ws://0.0.0.0:8080/chat');

socket.onopen = function (ev) {
    console.log('opened connection');
    console.log(ev);
};

socket.onmessage = function (ev) {
    console.log(ev);
    alert(ev.data);
};

```

**is Route and hasRoute methods**


1. **hasRoute:**
   Is there any client connecting via /chat route?.

2. **isRoute:**
   Is the current client connected via the /chat route?.



```php
class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[Override]
    public function run(): void
    {
        $this->hasRoute('/chat');//Is there any client connecting via /chat route?
        $this->isRoute('/chat'); //Is the current client connected via the /chat route?
    }
}


$socketServer = new SocketServer(Handler::class);
$socketServer->serve(
  '0.0.0.0',
   8080
);
```

### The host address of the client
Let's disconnect the socket connection of an ip address
<br/>
You can get the host or IP address of the connected client.
In this way, you can deny an IP address, for example, let's disconnect a client whose IP value is x.x.x.x.

```php

class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[\Override]
    public function run(): void
    {
        $host=$this->getRemoteHost();
        if('x.x.x.x'===$host){
            $this->disconnect($this->getClient());
        }
    }
}
```
### Get the status of the client

You can get any client status

```php

class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[\Override]
    public function run(): void
    {
        //type 1 using 
        
        $status=$this->getStatus();
        
        //type 2 using
        
        $clients=$this->findChannel('it.backend')->getClients();
        foreach($clients as $client){
            $status=$this->getStatus($client);
            print_r($status);
        }
      
    }
}

### More

```
### For more information
Now, if you want to add Channel Broadcast and Send classes, there is a way to do this. For example, let's create a method for Send and use it.
```php

class Handler extends AbstractSocketClientHandler
{

    /**
     */
    #[\Override]
    public function run(): void
    {
        Send::method(
            'test',
            static fn(Socket $client) => socket_write($client, Message::encode('test'))
        );


        Channel::method(
            'empty',
            static fn() => $this->clients=[])
        );

        $this->sendTo()->test($this->getClient());
        $this->findChannel('it.backend')->empty();
    }
}
```
If you pay attention, the 'test' and 'empty' methods do not actually exist, we simulated them as if they existed.


to be continued...
<br>
[|||||||||||||||]
