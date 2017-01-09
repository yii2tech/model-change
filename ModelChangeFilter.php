<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\modelchange;

use yii\base\Behavior;
use yii\base\Controller;
use yii\base\Event;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;

/**
 * ModelChangeBehavior provides ability of tracking the events of model state changes (e.g. save, delete etc.)
 * during the controller action execution.
 *
 * This behavior should be attached to [[\yii\web\Controller]] instance.
 *
 * @property \yii\web\Controller $owner owner controller instance.
 * @property string[] $modelClasses list of model classes, which should be tracked.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ModelChangeFilter extends Behavior
{
    /**
     * @event ActionEvent an event raised right before model has been changed.
     * This event will be triggered in the owner controller scope.
     */
    const EVENT_AFTER_MODEL_CHANGE = 'afterModelChange';

    /**
     * @var string[] list of model event names, which should be tracked.
     */
    public $modelEvents = [
        BaseActiveRecord::EVENT_AFTER_INSERT,
        BaseActiveRecord::EVENT_AFTER_UPDATE,
        BaseActiveRecord::EVENT_AFTER_DELETE,
        'afterSoftDelete',
        'afterRestore',
    ];

    /**
     * @var string[] list of model classes, which should be tracked.
     */
    private $_modelClasses;


    /**
     * Returns  list of model classes, which should be tracked.
     * If not set, in case owner controller provides `modelClass` property, its value will be used.
     * @return string[] list of model classes, which should be tracked.
     */
    public function getModelClasses()
    {
        if ($this->_modelClasses === null) {
            if ($this->owner !== null && isset($this->owner->modelClass)) {
                $this->_modelClasses = [
                    $this->owner->modelClass
                ];
            } else {
                $this->_modelClasses = [];
            }
        }
        return $this->_modelClasses;
    }

    /**
     * @param string[] $modelClasses list of model classes, which should be tracked.
     */
    public function setModelClasses($modelClasses)
    {
        $this->_modelClasses = $modelClasses;
    }

    /**
     * Attaches event handles for tracking model change events.
     * @param string $modelClass model class to be tracked.
     */
    protected function attachModelEventListeners($modelClass)
    {
        foreach ($this->modelEvents as $eventName) {
            Event::on($modelClass, $eventName, [$this, 'onModelEvent']);
        }
    }

    /**
     * Detaches event handlers, which have been setup by [[attachModelEventListeners()]].
     * @param string $modelClass tracked model class.
     */
    protected function detachModelEventListeners($modelClass)
    {
        foreach ($this->modelEvents as $eventName) {
            Event::off($modelClass, $eventName, [$this, 'onModelEvent']);
        }
    }

    /**
     * Responds to the model change event.
     * @param \yii\base\ModelEvent $event event instance.
     */
    public function onModelEvent($event)
    {
        if ($event instanceof AfterSaveEvent) {
            if (empty($event->changedAttributes)) {
                return;
            }
        }

        $this->afterModelChange($event->sender);
    }

    /**
     * This method is called after of the [[modelEvents]] events occurs at one of [[modelClasses]].
     * The default implementation will trigger an [[EVENT_AFTER_MODEL_CHANGE]] event.
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param \yii\base\Model|null $model model instance.
     */
    protected function afterModelChange($model)
    {
        $this->owner->trigger(self::EVENT_AFTER_MODEL_CHANGE, new ActionEvent($this->owner->action, ['model' => $model]));
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
        foreach ($this->getModelClasses() as $modelClass) {
            $this->attachModelEventListeners($modelClass);
        }
    }

    /**
     * Handles [[Controller::EVENT_AFTER_ACTION]] event, detaching model event listeners.
     * @param \yii\base\ActionEvent $event event instance.
     */
    public function afterAction($event)
    {
        foreach ($this->getModelClasses() as $modelClass) {
            $this->detachModelEventListeners($modelClass);
        }
    }
}