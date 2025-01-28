<?php

namespace app\controllers;

use app\models\Characters;
use stdClass;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CharactersController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return Response
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $authToken = $request->post('account_auth_token');
            $charName = $request->post('char_name');

            // Декодирование токена
            $decoded = JWT::decode($authToken, new Key(Yii::$app->params['jwtSecret'], 'HS256'));
            $userId = $decoded->sub;

            // Проверка наличия имени персонажа
            if (!$charName) {
                return $this->asJson(['status' => 'error', 'message' => 'Не указано имя персонажа']);
            }

            // Создание новой модели персонажа
            $character = new Characters();
            $character->user_id = $userId;
            $character->name = $charName;
            $character->location_id = 1;
            $character->auth_token = $this->generateJwtToken($character);


            if ($character->validate() && $character->save()) {
                Characters::updateAll(['is_selected' => false, 'is_online' => false], ['user_id' => $character->user_id]);
                Characters::updateAll(['is_selected' => true, 'is_online' => true], ['id' => $character->id, 'user_id' => $character->user_id]);
                return $this->asJson(['status' => 'success', 'char_id' => $character->id, 'auth_token' => $character->auth_token, 'char_location_id' => $character->location_id]);
            } else {
                // Ошибки валидации
                return $this->asJson(['status' => 'error', 'errors' => $character->getErrors()]);
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
    public function actionGetList()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $authToken = $request->post('account_auth_token');

            // Декодирование токена
            $decoded = JWT::decode($authToken, new Key(Yii::$app->params['jwtSecret'], 'HS256'));
            $userId = $decoded->sub;

            // Загрузка списка персонажей пользователя
            $characters = Characters::find()
                ->where(['user_id' => $userId])
                ->all();

            // Формирование ответа
            $charsList = [];

            if ($characters) {
                foreach ($characters as $character) {
                    $token = $this->generateJwtToken($character);
                    $charsList[] = ['char_id' => $character->id, 'name' => $character->name, 'auth_token' => $token];
                }
                return $this->asJson(['status' => 'success', 'chars_list' => $charsList]);
            } else {
                return $this->asJson(['status' => 'success', 'chars_list' => $charsList]);
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
    public function actionSelectActive()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $authToken = $request->post('account_auth_token');
            $characterId = $request->post('char_id');

            // Декодирование токена
            $decoded = JWT::decode($authToken, new Key(Yii::$app->params['jwtSecret'], 'HS256'));
            $userId = $decoded->sub;

            // Обновление статуса "is_selected" для персонажей пользователя
            Characters::updateAll(['is_selected' => false, 'is_online' => false], ['user_id' => $userId]);
            Characters::updateAll(['is_selected' => true, 'is_online' => true], ['id' => $characterId, 'user_id' => $userId]);
            // Получение персонажа по ID
            $character = Characters::findOne(['id' => $characterId, 'user_id' => $userId]);

            // Проверка, найден ли персонаж
            if ($character) {
                // Генерируем токен персонажа
                $characterToken = $this->generateJwtToken($character);

                return $this->asJson([
                    'status' => 'success',
                    'char_id' => $character->id,
                    'auth_token' => $characterToken,
                    'char_location_id' => $character->location_id
                ]);

            } else {
                return $this->asJson(['status' => 'error', 'message' => 'Персонаж не найден']);
            }
        } else {
            return $this->asJson(['status' => 'error', 'message' => 'Некорректный метод запроса']);
        }
    }


    private function generateJwtToken($character)
    {
        $key = Yii::$app->params['jwtSecret'];
        $payload = [
            'iss' => 'spellcraft',
            'char_id' => $character->id, // ID персонажа
            'char_name' => $character->name, // ID пользователя
            'user_id' => $character->user_id, // ID пользователя
            'iat' => time(),
            'exp' => time() + (60 * 60),
            'kid' => 'characters'
        ];
        return JWT::encode($payload, $key, 'HS256');
    }



}
