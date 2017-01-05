<?php

namespace yii2tech\tests\unit\modelchange\data;

/**
 * Test controller class.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Controller extends \yii\web\Controller
{
    /**
     * @var string related model class.
     */
    public $modelClass;
    /**
     * @var array actions configuration, which will be returned by [[actions()]] method.
     */
    public $actions = [];

    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        return [
            'view' => $view,
            'params' => $params,
        ];
    }

    /**
     * @inheritdoc
     */
    public function redirect($url, $statusCode = 302)
    {
        return [
            'url' => $url,
            'statusCode' => $statusCode,
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return $this->actions;
    }
}