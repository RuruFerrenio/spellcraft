<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Items".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property string $type
 * @property string $rarity
 * @property float $weight
 * @property int $stackable
 * @property string|null $effect
 * @property int $value
 *
 * @property ItemLocations[] $itemLocations
 */
class Items extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type', 'rarity', 'weight'], 'required'],
            [['description'], 'string'],
            [['weight'], 'number'],
            [['stackable', 'value'], 'integer'],
            [['effect'], 'safe'],
            [['name', 'icon'], 'string', 'max' => 255],
            [['type', 'rarity'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'icon' => 'Icon',
            'type' => 'Type',
            'rarity' => 'Rarity',
            'weight' => 'Weight',
            'stackable' => 'Stackable',
            'effect' => 'Effect',
            'value' => 'Value',
        ];
    }

    /**
     * Gets query for [[ItemLocations]].
     *
     * @return \yii\db\ActiveQuery|ItemLocationsQuery
     */
    public function getItemLocations()
    {
        return $this->hasMany(ItemLocations::class, ['item_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ItemsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ItemsQuery(get_called_class());
    }
}
