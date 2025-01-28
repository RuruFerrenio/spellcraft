<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Characters]].
 *
 * @see Characters
 */
class CharactersQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Characters[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Characters|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
