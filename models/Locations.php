<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "locations".
 *
 * @property int $ID
 * @property string $NAME
 * @property string|null $DESCRIPTION
 * @property string $TYPE
 * @property float $COORDINATES_X
 * @property float $COORDINATES_Y
 * @property string|null $ADDITIONAL_DATA
 *
 * @property Characters[] $characters
 * @property Users[] $users
 */
class Locations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'locations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['NAME', 'TYPE', 'COORDINATES_X', 'COORDINATES_Y'], 'required'],
            [['DESCRIPTION'], 'string'],
            [['COORDINATES_X', 'COORDINATES_Y'], 'number'],
            [['ADDITIONAL_DATA'], 'safe'],
            [['NAME', 'TYPE'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'NAME' => 'Name',
            'DESCRIPTION' => 'Description',
            'TYPE' => 'Type',
            'COORDINATES_X' => 'Coordinates X',
            'COORDINATES_Y' => 'Coordinates Y',
            'ADDITIONAL_DATA' => 'Additional Data',
        ];
    }

    /**
     * Gets query for [[Characters]].
     *
     * @return \yii\db\ActiveQuery|CharactersQuery
     */
    public function getCharacters()
    {
        return $this->hasMany(Characters::class, ['location_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::class, ['location_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return LocationsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LocationsQuery(get_called_class());
    }
}
