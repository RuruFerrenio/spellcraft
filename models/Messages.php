<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "messages".
 *
 * @property int $id
 * @property int|null $room_id
 * @property int $sender_character_id
 * @property int $receiver_character_id
 * @property string $message
 * @property string|null $created_at
 *
 * @property Characters $receiverCharacter
 * @property Characters $senderCharacter
 */
class Messages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'messages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['room_id', 'sender_character_id', 'receiver_character_id'], 'integer'],
            [['sender_character_id', 'receiver_character_id', 'message'], 'required'],
            [['message'], 'string'],
            [['created_at'], 'safe'],
            [['sender_character_id'], 'exist', 'skipOnError' => true, 'targetClass' => Characters::class, 'targetAttribute' => ['sender_character_id' => 'id']],
            [['receiver_character_id'], 'exist', 'skipOnError' => true, 'targetClass' => Characters::class, 'targetAttribute' => ['receiver_character_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_id' => 'Room ID',
            'sender_character_id' => 'Sender Character ID',
            'receiver_character_id' => 'Receiver Character ID',
            'message' => 'Message',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[ReceiverCharacter]].
     *
     * @return \yii\db\ActiveQuery|CharactersQuery
     */
    public function getReceiverCharacter()
    {
        return $this->hasOne(Characters::class, ['id' => 'receiver_character_id']);
    }

    /**
     * Gets query for [[SenderCharacter]].
     *
     * @return \yii\db\ActiveQuery|CharactersQuery
     */
    public function getSenderCharacter()
    {
        return $this->hasOne(Characters::class, ['id' => 'sender_character_id']);
    }

    /**
     * {@inheritdoc}
     * @return MessagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessagesQuery(get_called_class());
    }
}
