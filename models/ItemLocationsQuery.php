<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[ItemLocations]].
 *
 * @see ItemLocations
 */
class ItemLocationsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ItemLocations[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ItemLocations|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
