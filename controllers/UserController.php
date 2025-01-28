<?php

namespace app\controllers;

use app\models\Users;
use Firebase\JWT\JWT;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class UserController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * @return Response
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $login = $request->post('login');
            $password = $request->post('password');

            // Поиск пользователя по логину
            $user = Users::findOne(['username' => $login]);

            if ($user) {
                // Проверка пароля с помощью password_verify
                if (password_verify($password, $user->password_hash)) {
                    // Авторизация успешна
                    $token = $this->generateJwtToken($user);
                    $user->auth_token = $token;
                    $user->save();
                    //Yii::$app->user->login($user);
                    header('Content-Length: ' . strlen(json_encode(['status' => 'success', 'user_id' => $user->id, 'auth_token' => $user->auth_token])));
                    return $this->asJson(['status' => 'success', 'user_id' => $user->id, 'auth_token' => $user->auth_token]);
                } else {
                    // Неверный пароль
                    return $this->asJson(['status' => 'error', 'errors' => 'Неверный пароль' ]);
                }
            } else {
                // Пользователь не найден
                return $this->asJson(['status' => 'error', 'errors' => 'Пользователь не найден']);
            }
        } else {
            // Запрос не POST
            return $this->asJson(['status' => 'error', 'message' => 'Invalid request method']);
        }
    }

    /**
     * @return Response
     * @throws \yii\db\Exception
     */
    public function actionRegistration()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {

            $login = $request->post('login');
            $email = $request->post('email');
            $password = $request->post('password');
            $passwordControl = $request->post('password_check');

            // Проверка совпадения паролей
            if ($password !== $passwordControl) {
                return $this->asJson(['status' => 'error', 'errors' => ['password' => 'Пароли не совпадают']]);
            }
            // Создание новой модели пользователя
            $user = new Users();
            $user->username = $login;
            $user->email = $email;
            $user->password_hash = password_hash($password, PASSWORD_BCRYPT);
            // Сохранение токена в модель пользователя
            Yii::info('Регистрация');

            // Валидация модели
            if ($user->validate() && $user->save()) {
                $token = $this->generateJwtToken($user);
                $user->auth_token = $token;
                Yii::info('Валидация успешна');
                $user->save();
                // Успешная регистрация
                return $this->asJson(['status' => 'success', 'message' => 'Регистрация прошла успешно', 'auth_token' => $user->auth_token, 'user_id' => $user->id]);
            } else {
                // Ошибки валидации
                return $this->asJson(['status' => 'error', 'errors' => $user->getErrors()]);
            }
        } else {
            // Некорректный метод запроса
            return $this->asJson(['status' => 'error', 'message' => 'Некорректный метод запроса']);
        }
    }

    private function generateJwtToken($user)
    {
        $key = Yii::$app->params['jwtSecret']; // Замените на ваш секретный ключ
        $payload = [
            'iss' => 'spellcraft', // Издатель
            'sub' => $user->id, // Идентификатор пользователя
            'iat' => time(), // Время выдачи
            'exp' => time() + (60 * 60), // Время истечения срока действия
            'kid' => 1 // Добавьте `kid`
        ];
        Yii::info($payload);
        return JWT::encode($payload, $key, 'HS256');
    }


}
