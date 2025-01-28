<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use app\models\Characters;

// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

/**
 * LocationOperator.php
 * Manages location connections and character data
 */
class LocationOperator implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $locations;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->locations = [];
        $this->users = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        // Проверка данных сообщения
        if (!isset($data['type']) || !isset($data['data'])) {
            return;
        }

        switch ($data['type']) {
            case 'join':
                $this->joinLocation($from, $data['data']['userId'], $data['data']['locationId']);
                break;
            case 'leave':
                $this->leaveLocation($from, $data['data']['userId']);
                break;
            default:
                // Handle other message types if needed
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->leaveLocation($conn);
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    protected function joinLocation(ConnectionInterface $conn, $userId, $locationId) {
        // Find the user's ID (assuming it's sent with the connection data)

        // Update the user's location in the database
        $character = Characters::findOne($userId);
        $character->location_id = $locationId;
        $character->save();

        // Store the user's connection in the location
        if (!isset($this->locations[$locationId])) {
            $this->locations[$locationId] = [];
        }
        $this->locations[$locationId][$userId] = $conn;

        // Send initial location data to the client
        $locationData = [
            'type' => 'location_data',
            'data' => [
                'locationId' => $locationId,
                'characters' => $this->getLocationCharacters($locationId),
                'enemies' => $this->getLocationEnemies($locationId), // Get enemies from your data
            ],
        ];
        $conn->send(json_encode($locationData));

        // Send information about the new user to other clients in the location
        $this->broadcastToLocation($locationId, [
            'type' => 'user_joined',
            'data' => [
                'userId' => $userId,
                // ... additional user data if needed
            ],
        ], $conn);
    }

    protected function leaveLocation(ConnectionInterface $conn, $userId) {
        // Find the user's ID

        // Find the user's current location
        $locationId = null;
        foreach ($this->locations as $locId => $connections) {
            if (isset($connections[$userId])) {
                $locationId = $locId;
                break;
            }
        }

        if ($locationId) {
            // Remove the user from the location
            unset($this->locations[$locationId][$userId]);

            // Update the user's location in the database
            $character = Characters::findOne($userId);
            $character->location_id = null; // Or set it to a default location
            $character->save();

            // Broadcast information about the user leaving to other clients
            $this->broadcastToLocation($locationId, [
                'type' => 'user_left',
                'data' => [
                    'userId' => $userId,
                    // ... additional user data if needed
                ],
            ]);
        }
    }

    protected function broadcastToLocation($locationId, $message, $excludeConnection = null) {
        if (!isset($this->locations[$locationId])) {
            return;
        }

        foreach ($this->locations[$locationId] as $conn) {
            if ($conn !== $excludeConnection) {
                $conn->send(json_encode($message));
            }
        }
    }

    protected function getLocationCharacters($locationId) {
        // Fetch characters from your database based on locationId
        // Return an array of character data
        $characters = Characters::find()
            ->where(['location_id' => $locationId])
            ->all();

        // Format character data for client
        $characterData = [];
        foreach ($characters as $character) {
            $characterData[] = [
                'id' => $character->id,
                // ... other character attributes
            ];
        }

        return $characterData;
    }

    protected function getLocationEnemies($locationId) {
        // Fetch enemies from your database based on locationId
        // Return an array of enemy data
        // ...
        return [];
    }
}

