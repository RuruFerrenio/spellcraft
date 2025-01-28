<?php

namespace app\commands;

use app\models\Users;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class UserController extends Controller
{
    public function actionLogin($login, $password)
    {
        Yii::info('qweqweqw');
        // Проверяем, переданы ли аргументы
        if (empty($login) || empty($password)) {
            Console::stderr("Ошибка: Необходимо указать логин и пароль.\n");
            return 1;
        }

        $model = new Users();
        $model->username = $login;
        $model->password_hash = password_hash($password, PASSWORD_BCRYPT);

        if ($model->validate() && $model->login()) {
            Console::stdout("Успешная авторизация.\n");
            return 0; // Возвращаем 0 при успехе
        } else {
            Console::stderr("Неверный логин или пароль.\n");
            return 1; // Возвращаем 1 при ошибке
        }
    }

    public function actionTest()
    {
        echo "lol";
    }
}
