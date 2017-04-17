<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\modelchange;

use yii\base\ActionFilter;

/**
 * ModelChangeFilter provides ability of tracking the events of model state changes (e.g. save, delete etc.)
 * during the controller action execution.
 *
 * This filter can be attached to [[\yii\web\Controller]] or [[\yii\base\Module]] (including [[\yii\base\Application]]
 * itself) instance.
 *
 * Controller configuration example:
 *
 * ```php
 * class PageController extends \yii\web\Controller
 * {
 *     public $modelClass = 'app\models\Page'; // in case `modelClass` property exists, its value will be picked up automatically
 *
 *     public function behaviors()
 *     {
 *         return [
 *             'modelChange' => [
 *                 'class' => ModelChangeFilter::className(),
 *                 'except' => [
 *                     'index',
 *                     'view'
 *                 ],
 *                 'afterModelChange' => function ($event) {
 *                     Yii::$app->getSession()->set('cacheFlushRequired', true);
 *                 },
 *             ],
 *         ];
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'as modelChange' => [
 *         'class' => 'yii2tech\modelchange\ModelChangeFilter',
 *         'modelClasses' => [
 *             'app\models\Page',
 *             'app\models\PageContent',
 *             'app\models\MenuItem',
 *         ],
 *         'afterModelChange' => function ($event) {
 *             Yii::$app->getSession()->set('cacheFlushRequired', true);
 *         },
 *     ],
 *     // ...
 * ];
 * ```
 *
 * @see ModelChangeTrait
 *
 * @property \yii\web\Controller $owner owner controller instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ModelChangeFilter extends ActionFilter
{
    use ModelChangeTrait;
    
    /**
     * @event ActionEvent an event raised right before model has been changed.
     * This event will be triggered in the owner controller scope.
     */
    const EVENT_AFTER_MODEL_CHANGE = 'afterModelChange';

    /**
     * @var callable|null a PHP callback, which should be executed after model has been changed.
     * Callback should match the following signature:
     *
     * ```php
     * function (\yii2tech\modelchange\ActionEvent $event) {}
     * ```
     *
     * Note that you may use [[EVENT_AFTER_MODEL_CHANGE]] event for the same effect.
     */
    public $afterModelChange;


    /**
     * Provides default value for [[modelClasses]].
     * @return array default model classes.
     */
    protected function defaultModelClasses()
    {
        if ($this->owner !== null && isset($this->owner->modelClass)) {
            return [
                $this->owner->modelClass
            ];
        }
        return [];
    }

    /**
     * This method is called after of the [[modelEvents]] events occurs at one of [[modelClasses]].
     * The default implementation will trigger an [[EVENT_AFTER_MODEL_CHANGE]] event.
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param \yii\base\ModelEvent $event model event instance.
     */
    protected function afterModelChange($event)
    {
        $event = new ActionEvent($this->owner->action, ['modelEvent' => $event]);
        if ($this->afterModelChange !== null) {
            call_user_func($this->afterModelChange, $event);
        }
        $this->owner->trigger(self::EVENT_AFTER_MODEL_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->attachModelEventListeners();
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $this->detachModelEventListeners();
        return parent::afterAction($action, $result);
    }
}