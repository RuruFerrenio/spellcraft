<?php

namespace app\controllers;

use app\models\Characters;
use app\models\Inventory;
use app\models\Items;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;
use yii\web\Response;

class InventoryController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return Response
     * @throws \yii\db\Exception
     */
    public function actionGetList()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $authToken = $request->post('char_token');

            // Декодирование токена
            $decoded = JWT::decode($authToken, new Key(Yii::$app->params['jwtSecret'], 'HS256'));
            $characterId = $decoded->char_id;

            // Получение предметов из инвентаря персонажа
            $inventoryItems = Inventory::find()
                ->joinWith('item')
                ->where(['CharacterId' => $characterId])
                ->all();

            // Формирование ответа
            $itemsList = [];

            if ($inventoryItems) {
                foreach ($inventoryItems as $inventoryItem) {
                    $itemsList[] = [
                        'id' => $inventoryItem->item->id,
                        'name' => $inventoryItem->item->name,
                        'description' => $inventoryItem->item->description,
                        'quantity' => $inventoryItem->quantity,
                        // Добавьте сюда другие свойства предмета, которые вам нужны
                    ];
                }
                return $this->asJson(['status' => 'success', 'items_list' => $itemsList]);
            } else {
                return $this->asJson(['status' => 'success', 'items_list' => $itemsList]);
            }
        } else {
            // Некорректный метод запроса
            return $this->asJson(['status' => 'error', 'message' => 'Некорректный метод запроса']);
        }
    }

    /**
     * @return Response
     * @throws \yii\db\Exception
     */
    public function actionRemoveItem()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $authToken = $request->post('char_token');
            $itemId = $request->post('item_id');
            $quantity = $request->post('quantity');

            // Декодирование токена
            $decoded = JWT::decode($authToken, new Key(Yii::$app->params['jwtSecret'], 'HS256'));
            $characterId = $decoded->char_id;

            // Поиск записи в инвентаре
            $inventoryItem = Inventory::findOne(['CharacterId' => $characterId, 'ItemId' => $itemId]);

            // Проверка, существует ли запись
            if ($inventoryItem) {
                // Проверка количества
                if ($inventoryItem->quantity >= $quantity) {
                    // Удаление или обновление записи в инвентаре
                    if ($inventoryItem->quantity === $quantity) {
                        $inventoryItem->delete();
                    } else {
                        $inventoryItem->quantity -= $quantity;
                        $inventoryItem->save();
                    }

                    return $this->asJson(['status' => 'success', 'message' => 'Предмет удален из инвентаря']);
                } else {
                    return $this->asJson(['status' => 'error', 'message' => 'Недостаточно предметов в инвентаре']);
                }
            } else {
                return $this->asJson(['status' => 'error', 'message' => 'Предмет не найден в инвентаре']);
            }
        } else {
            // Некорректный метод запроса
            return $this->asJson(['status' => 'error', 'message' => 'Некорректный метод запроса']);
        }
    }
}
