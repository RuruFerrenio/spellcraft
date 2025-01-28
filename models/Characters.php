<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "characters".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property int|null $location_id
 * @property string|null $auth_token
 * @property int|null $is_online
 * @property int|null $is_selected
 * @property int|null $visibility
 *
 * @property Locations $location
 * @property Messages[] $messages
 * @property Messages[] $messages0
 * @property Users $user
 */
class Characters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'characters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'location_id', 'is_online', 'is_selected', 'visibility'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['auth_token'], 'string', 'max' => 512],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Locations::class, 'targetAttribute' => ['location_id' => 'ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'location_id' => 'Location ID',
            'auth_token' => 'Auth Token',
            'is_online' => 'Is Online',
            'is_selected' => 'Is Selected',
            'visibility' => 'Visibility',
        ];
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery|LocationsQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Locations::class, ['ID' => 'location_id']);
    }

    /**
     * Gets query for [[Messages]].
     *
     * @return \yii\db\ActiveQuery|MessagesQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Messages::class, ['sender_character_id' => 'id']);
    }

    /**
     * Gets query for [[Messages0]].
     *
     * @return \yii\db\ActiveQuery|MessagesQuery
     */
    public function getMessages0()
    {
        return $this->hasMany(Messages::class, ['receiver_character_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return CharactersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CharactersQuery(get_called_class());
    }
}
