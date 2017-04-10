<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\modelchange;

use yii\base\Behavior;
use yii\base\Controller;

/**
 * ModelChangeBehavior provides ability of tracking the events of model state changes (e.g. save, delete etc.)
 * during the controller action execution.
 *
 * This behavior should be attached to [[\yii\web\Controller]] instance.
 *
 * @see ModelChangeTrait
 *
 * @property \yii\web\Controller $owner owner controller instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ModelChangeFilter extends Behavior
{
    use ModelChangeTrait;
    
    /**
     * @event ActionEvent an event raised right before model has been changed.
     * This event will be triggered in the owner controller scope.
     */
    const EVENT_AFTER_MODEL_CHANGE = 'afterModelChange';


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
        $this->owner->trigger(self::EVENT_AFTER_MODEL_CHANGE, new ActionEvent($this->owner->action, ['modelEvent' => $event]));
    }

    // Events :

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
            Controller::EVENT_AFTER_ACTION => 'afterAction',
        ];
    }

    /**
     * Handles [[Controller::EVENT_BEFORE_ACTION]] event, preparing model event listeners.
     * @param \yii\base\ActionEvent $event event instance.
     */
    public function beforeAction($event)
    {
        $this->attachModelEventListeners();
    }

    /**
     * Handles [[Controller::EVENT_AFTER_ACTION]] event, detaching model event listeners.
     * @param \yii\base\ActionEvent $event event instance.
     */
    public function afterAction($event)
    {
        $this->detachModelEventListeners();
    }
}