<?php

namespace yii2tech\tests\unit\modelchange\data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 * @property string $categoryId
 */
class Item extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
        ];
    }
}