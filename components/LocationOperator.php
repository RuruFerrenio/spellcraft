<?php

namespace app\components;

use app\models\Locations;
use app\models\Transitions;
use app\models\Items;
use app\models\ItemLocations;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use app\models\Characters;
use Yii;

class LocationOperator extends \yii\base\Component implements MessageComponentInterface
{
    protected $clients;
    protected $users;
    protected $locations;


    public function init()
    {
        parent::init();
        $this->clients = new \SplObjectStorage;
        $this->locations = [];
        $this->users = [];
    }

    /**
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    /**
     * @param ConnectionInterface $from
     * @param $msg
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        // Проверка данных сообщения
        if (!isset($data['type']) || !isset($data['data'])) {
            return;
        }

        $decodedTokenData = JWT::decode($data['data']['char_token'], new Key(Yii::$app->params['jwtSecret'], 'HS256'));

        $this->users[$from->resourceId] = $decodedTokenData; // Сохраняем данные токена

        $charId = $decodedTokenData->char_id;
        $charName = $decodedTokenData->char_name;
        $userId = $decodedTokenData->user_id;

        switch ($data['type']) {
            case 'join':
                $this->joinLocation($from, $decodedTokenData, $data['data']['location_id'], $data['data']['from_location_id']);
                break;
            case 'leave':
                $this->leaveLocation($from, $decodedTokenData);
                break;
            case 'search':
                $this->searchLocation($from, $decodedTokenData, $data['data']['location_id']);
                break;
            default:
                break;
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onClose(ConnectionInterface $conn)
    {
        if (isset($this->users[$conn->resourceId])) {
            $decodedTokenData = $this->users[$conn->resourceId]; // Получаем данные токена
            $this->leaveLocation($conn, $decodedTokenData);
            unset($this->users[$conn->resourceId]); // Удаляем данные токена
        }
        $this->clients->detach($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }

    /**
     * @param ConnectionInterface $conn
     * @param $decodedTokenData
     * @param $locationId
     * @param $fromlocationId
     * @return void
     */
    protected function joinLocation(ConnectionInterface $conn, $decodedTokenData, $locationId, $fromlocationId)
    {
        $character = Characters::findOne($decodedTokenData->char_id);
        $character->location_id = $locationId;
        $character->save();

        if (!isset($this->locations[$locationId])) {
            $this->locations[$locationId] = [];
        }
        $this->locations[$locationId][$decodedTokenData->char_id] = $conn;

        $locationData = [
            'type' => 'location_data',
            'data' => [
                'location' => $this->getLocationDetailInfo($locationId),
                'transitionGraph' => $this->getTransitionGraph($locationId, $fromlocationId),
                'locationId' => $locationId,
                'characters' => $this->getLocationCharacters($locationId, $decodedTokenData->char_id),
                'enemies' => $this->getLocationEnemies($locationId),
                'items' => $this->getLocationItems($locationId),
            ],
        ];

        $conn->send(json_encode($locationData));

        $this->broadcastToLocation($locationId, [
            'type' => 'char_joined',
            'data' => [
                $decodedTokenData->char_id => [
                    'id' => $decodedTokenData->char_id,
                    'name' => $decodedTokenData->char_name,
                ]
            ],
        ], $conn);
    }

    /**
     * @param int $locationId
     * @return array
     */
    protected function getLocationItems(int $locationId): array
    {
        $itemsData = ItemLocations::find()
            ->where(['location_id' => $locationId])
            ->with('item')
            ->all();

        $items = [];
        foreach ($itemsData as $item) {
            if($this->chekDetected($item->visibility)){
                $items[] = [
                    'id' => $item->item->id,
                    'name' => $item->item->name,
                    'description' => $item->item->description,
                    'visibility' => $item->visibility,
                ];
            }
        }

        return $items;
    }

    /**
     * @param ConnectionInterface $conn
     * @param $decodedTokenData
     * @param $locationId
     * @return void
     */
    protected function searchLocation(ConnectionInterface $conn, $decodedTokenData, $locationId)
    {

        $locationData = [
            'type' => 'location_data',
            'data' => [
                'location' => $this->getLocationDetailInfo($locationId),
                'transitionGraph' => $this->getTransitionGraph($locationId),
                'locationId' => $locationId,
                'characters' => $this->getLocationCharacters($locationId, $decodedTokenData->char_id),
                'enemies' => $this->getLocationEnemies($locationId),
                'items' => $this->getLocationItems($locationId),
            ],
        ];

        $conn->send(json_encode($locationData));
    }

    /**
     * @param ConnectionInterface $conn
     * @param $decodedTokenData
     * @return void
     */
    protected function leaveLocation(ConnectionInterface $conn, $decodedTokenData)
    {
        $locationId = null;
        foreach ($this->locations as $locId => $connections) {
            if (isset($connections[$decodedTokenData->char_id])) {
                $locationId = $locId;
                break;
            }
        }

        if ($locationId) {

            unset($this->locations[$locationId][$decodedTokenData->char_id]);

            $this->broadcastToLocation($locationId, [
                'type' => 'char_leave',
                'data' => [
                    'id' => $decodedTokenData->char_id,
                    'name' => $decodedTokenData->char_name,
                ],
            ]);
        }
    }

    /**
     * @param $locationId
     * @param $message
     * @param $excludeConnection
     * @return void
     */
    protected function broadcastToLocation($locationId, $message, $excludeConnection = null)
    {
        if (!isset($this->locations[$locationId])) {
            return;
        }

        foreach ($this->locations[$locationId] as $conn) {
            if ($conn !== $excludeConnection) {
                $conn->send(json_encode($message));
            }
        }
    }

    /**
     * @param $locationId
     * @param $clientCharId
     * @return array
     */
    protected function getLocationCharacters($locationId, $clientCharId)
    {
        $characters = Characters::find()
            ->where(['location_id' => $locationId, 'is_online' => true, 'is_selected' => true])
            ->all();

        $characterData = [];
        foreach ($characters as $character) {
            if($this->chekDetected($character->visibility) || ($character->id === $clientCharId)){
                $characterData[$character->id] = [
                    'id' => $character->id,
                    'name' => $character->name
                ];
            }
        }

        return $characterData;
    }

    /**
     * @param $locationId
     * @return array|null
     */
    protected function getLocationDetailInfo($locationId)
    {
        // Получаем данные о локации из базы данных
        $location = Locations::findOne($locationId);

        // Проверяем, найдена ли локация
        if (!$location) {
            return null; // Или вернуть ошибку
        }

        // Форматируем данные для клиента
        $locationData = [
            'id' => $location->ID,
            'name' => $location->NAME,
            'description' => $location->DESCRIPTION,
            // ... другие атрибуты локации
        ];

        return $locationData;
    }

    /**
     * @param $locationId
     * @param $fromLocationId
     * @return array
     */
    protected function getTransitionGraph($locationId, $fromLocationId = null)
    {
        // Получаем переходы из текущей локации
        $transitionsFrom = Transitions::find()
            ->where(['LOCATION_FROM_ID' => $locationId])
            ->all();

        // Получаем переходы на текущую локацию
        $transitionsTo = Transitions::find()
            ->where(['LOCATION_TO_ID' => $locationId])
            ->all();

        // Объединяем данные
        $transitionGraph = [];

        // Переходы из текущей локации
        foreach ($transitionsFrom as $transition) {

            if($this->chekDetected($transition->visibility) || ($transition->LOCATION_TO_ID == $fromLocationId)) {

                $toLocation = Locations::findOne($transition->LOCATION_TO_ID);
                $toLocationName = $toLocation ? $toLocation->NAME : null;

                $transitionGraph[] = [
                    'location_to_id' => $transition->LOCATION_TO_ID,
                    'location_from_id' => $transition->LOCATION_FROM_ID,
                    'location_name' => $toLocationName,
                    'transition_time' => $transition->TRANSITION_TIME,
                    'type' => 'outgoing'
                ];
            }

        }

        // Переходы на текущую локацию
        foreach ($transitionsTo as $transition) {

            if($this->chekDetected($transition->visibility) || ($transition->LOCATION_FROM_ID == $fromLocationId)) {

                $fromLocation = Locations::findOne($transition->LOCATION_FROM_ID);
                $fromLocationName = $fromLocation ? $fromLocation->NAME : null;

                $transitionGraph[] = [
                    'location_to_id' => $transition->LOCATION_TO_ID,
                    'location_from_id' => $transition->LOCATION_FROM_ID,
                    'location_name' => $fromLocationName,
                    'transition_time' => $transition->TRANSITION_TIME,
                    'type' => 'incoming'
                ];
            }
        }

        return $transitionGraph;
    }

    /**
     * @param $locationId
     * @return array
     */
    protected function getLocationEnemies($locationId)
    {
        return [];
    }

    /**
     * @param $characterVisibility
     * @return bool
     */
    // Функция для проверки обнаружения персонажа
    private function chekDetected($characterVisibility)
    {
        // Генерируем случайное число от 0 до 100
        $randomChance = rand(0, 100);
        // Проверяем, была ли обнаружена заметность
        return $randomChance <= $characterVisibility;
    }

}

