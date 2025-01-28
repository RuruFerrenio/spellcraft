<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transitions".
 *
 * @property int $ID
 * @property int $LOCATION_FROM_ID
 * @property int $LOCATION_TO_ID
 * @property int $TRANSITION_TIME
 * @property string|null $REQUIREMENTS
 * @property int|null $visibility
 */
class Transitions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transitions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['LOCATION_FROM_ID', 'LOCATION_TO_ID', 'TRANSITION_TIME'], 'required'],
            [['LOCATION_FROM_ID', 'LOCATION_TO_ID', 'TRANSITION_TIME', 'visibility'], 'integer'],
            [['REQUIREMENTS'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'LOCATION_FROM_ID' => 'Location From ID',
            'LOCATION_TO_ID' => 'Location To ID',
            'TRANSITION_TIME' => 'Transition Time',
            'REQUIREMENTS' => 'Requirements',
            'visibility' => 'Visibility',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TransitionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TransitionsQuery(get_called_class());
    }
}
