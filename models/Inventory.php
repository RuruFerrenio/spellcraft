<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Inventory".
 *
 * @property int $id
 * @property int $characterId
 * @property int $itemId
 * @property int $quantity
 *
 * @property Characters $character
 * @property Items $item
 */
class Inventory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Inventory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['characterId', 'itemId', 'quantity'], 'required'],
            [['characterId', 'itemId', 'quantity'], 'integer'],
            [['characterId'], 'exist', 'skipOnError' => true, 'targetClass' => Characters::class, 'targetAttribute' => ['characterId' => 'id']],
            [['itemId'], 'exist', 'skipOnError' => true, 'targetClass' => Items::class, 'targetAttribute' => ['itemId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'characterId' => 'Character ID',
            'itemId' => 'Item ID',
            'quantity' => 'Quantity',
        ];
    }

    /**
     * Gets query for [[Character]].
     *
     * @return \yii\db\ActiveQuery|CharactersQuery
     */
    public function getCharacter()
    {
        return $this->hasOne(Characters::class, ['id' => 'characterId']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery|ItemsQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::class, ['id' => 'itemId']);
    }

    /**
     * {@inheritdoc}
     * @return InventoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InventoryQuery(get_called_class());
    }
}
