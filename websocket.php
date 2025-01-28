<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class MyChat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        print_r($e);
        foreach ($this->clients as $client) {
            if ($conn != $client) {
                $client->send('Пшел отседа');
            }
        }
        $conn->close();
    }
}

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App('90.156.171.73', 8093, '0.0.0.0');
$app->route('/chat', new MyChat, array('*'));
$app->route('/echo', new Ratchet\Server\EchoServer, array('*'));
$app->run();