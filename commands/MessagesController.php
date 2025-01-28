<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Messages;
use yii\db\Exception;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

/**
 * Консольная команда для работы с моделью Messages
 */
class MessagesController extends Controller
{
    /**
     * Создает новое сообщение
     *
     * @param int $roomId Номер комнаты
     * @param int $senderCharacterId ID персонажа-отправителя
     * @param int $receiverCharacterId ID персонажа-получателя
     * @param string $message Текст сообщения
     * @return int Код завершения
     */
    public function actionCreate(
        int $roomId,
        int $senderCharacterId,
        int $receiverCharacterId,
        string $message
    ): int
    {
        $model = new Messages();
        $model->room_id = $roomId;
        $model->sender_character_id = $senderCharacterId;
        $model->receiver_character_id = $receiverCharacterId;
        $model->message = $message;

        if ($model->save()) {
            $this->stdout("Сообщение успешно создано.n", BaseConsole::FG_GREEN);
            return ExitCode::OK;
        } else {
            $this->stderr("Ошибка при создании сообщения:n" . print_r($model->getErrors(), true) . "n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Обновляет сообщение
     *
     * @param int $id ID сообщения
     * @param int|null $roomId Номер комнаты (необязательно)
     * @param int|null $senderCharacterId ID персонажа-отправителя (необязательно)
     * @param int|null $receiverCharacterId ID персонажа-получателя (необязательно)
     * @param string|null $message Текст сообщения (необязательно)
     * @return int Код завершения
     * @throws Exception
     */
    public function actionUpdate(
        int $id,
        int $roomId = null,
        int $senderCharacterId = null,
        int $receiverCharacterId = null,
        string $message = null
    ): int
    {
        $model = Messages::findOne($id);
        if (!$model) {
            $this->stderr("Сообщение с ID $id не найдено.n", BaseConsole::FG_RED);
            return ExitCode::DATAERR;
        }

        if ($roomId !== null) {
            $model->room_id = $roomId;
        }
        if ($senderCharacterId !== null) {
            $model->sender_character_id = $senderCharacterId;
        }
        if ($receiverCharacterId !== null) {
            $model->receiver_character_id = $receiverCharacterId;
        }
        if ($message !== null) {
            $model->message = $message;
        }

        if ($model->save()) {
            $this->stdout("Сообщение успешно обновлено.n", BaseConsole::FG_GREEN);
            return ExitCode::OK;
        } else {
            $this->stderr("Ошибка при обновлении сообщения:n" . print_r($model->getErrors(), true) . "n", BaseConsole::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Удаляет сообщение
     *
     * @param int $id ID сообщения
     * @return int Код завершения
     */
    public function actionDelete(int $id): int
    {
        $model = Messages::findOne($id);
        if (!$model) {
            $this->stderr("Сообщение с ID $id не найдено.n", BaseConsole::FG_RED);
            return ExitCode::DATAERR;
        }

        if ($model->delete()) {
            $this->stdout("Сообщение успешно удалено.n", BaseConsole::FG_GREEN);
            return ExitCode::OK;
        } else {
            $this->stderr("Ошибка при удалении сообщения.n", BaseConsole::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Выводит список сообщений
     *
     * @return int Код завершения
     */
    public function actionList(): int
    {
        $models = Messages::find()->all();
        if (empty($models)) {
            $this->stdout("Сообщений нет.n", BaseConsole::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout("Список сообщений:n", BaseConsole::FG_GREEN);
        foreach ($models as $model) {
            $this->stdout("ID: {$model->id}, Комната: {$model->room_id}, Отправитель: {$model->sender_character_id}, Получатель: {$model->receiver_character_id}, Сообщение: {$model->message}n", Console::FG_GREEN);
        }

        return ExitCode::OK;
    }
}
