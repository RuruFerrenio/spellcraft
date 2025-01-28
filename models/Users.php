<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $location_id
 * @property string|null $auth_token
 *
 * @property Characters[] $characters
 * @property Locations $location
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password_hash', 'email'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['location_id'], 'integer'],
            [['username', 'password_hash', 'email', 'auth_token'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['location_id'], 'exist', 'skipOnError' => true, 'targetClass' => Locations::class, 'targetAttribute' => ['location_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'email' => 'Email',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'location_id' => 'Location ID',
            'auth_token' => 'Auth Token',
        ];
    }

    /**
     * Gets query for [[Characters]].
     *
     * @return \yii\db\ActiveQuery|CharactersQuery
     */
    public function getCharacters()
    {
        return $this->hasMany(Characters::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery|LocationsQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Locations::class, ['id' => 'location_id']);
    }

    /**
     * {@inheritdoc}
     * @return UsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UsersQuery(get_called_class());
    }
}
