<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Transitions]].
 *
 * @see Transitions
 */
class TransitionsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Transitions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Transitions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
