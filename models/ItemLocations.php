<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ItemLocations".
 *
 * @property int $id
 * @property int $item_id
 * @property int $location_id
 * @property int $visibility
 *
 * @property Items $item
 * @property Locations $location
 */
class ItemLocations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ItemLocations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'location_id'], 'required'],
            [['item_id', 'location_id', 'visibility'], 'integer'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::class, 'targetAttribute' => ['item_id' => 'id']],
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
            'item_id' => 'Item ID',
            'location_id' => 'Location ID',
            'visibility' => 'Visibility',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery|ItemsQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::class, ['id' => 'item_id']);
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
     * {@inheritdoc}
     * @return ItemLocationsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemLocationsQuery(get_called_class());
    }
}
