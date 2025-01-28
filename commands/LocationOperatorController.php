<?php

namespace app\commands;

use LocationOperator;
use Yii;
use yii\console\Controller;
use Ratchet\App; // Подключаем Ratchet


class LocationOperatorController extends Controller
{
    public function actionStart()
    {
        $locationOperator = Yii::$app->locationOperator;
        // Запуск веб-сокета
        $app = new App('project-spellcraft.ru', 8093, '0.0.0.0'); // Указываем адрес и порт
        $app->route('/location', $locationOperator, array('*'));
        $app->run();
    }
}