<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\modelchange;

use yii\base\Event;
use yii\base\NotSupportedException;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;

/**
 * ModelChangeTrait provides the basic functionality of tracking the events of model state changes (e.g. save, delete etc.).
 * It can be used during composition of more specific classes.
 *
 * @property string[] $modelClasses list of model classes, which should be tracked.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ModelChangeTrait
{
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
     * @var bool whether to check, if [[AfterSaveEvent::$changedAttributes]] is not empty before [[afterModelChange()]] invocation.
     * If enabled model will be considered as 'changed' only if there is at least one changed attribute.
     * If disabled any saved model will be considered as changed.
     */
    public $checkChangedAttributes = true;

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
            $this->_modelClasses = $this->defaultModelClasses();
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
     * Provides default value for [[modelClasses]].
     * You may override this method inside the trait owner class, providing some custom logic, which determines
     * model class names.
     * @return array default model classes.
     */
    protected function defaultModelClasses()
    {
        return [];
    }

    /**
     * Attaches event handles for tracking model change events.
     */
    protected function attachModelEventListeners()
    {
        foreach ($this->getModelClasses() as $modelClass) {
            foreach ($this->modelEvents as $eventName) {
                Event::on($modelClass, $eventName, [$this, 'onModelEvent']);
            }
        }
    }

    /**
     * Detaches event handlers, which have been setup by [[attachModelEventListeners()]].
     */
    protected function detachModelEventListeners()
    {
        foreach ($this->getModelClasses() as $modelClass) {
            foreach ($this->modelEvents as $eventName) {
                Event::off($modelClass, $eventName, [$this, 'onModelEvent']);
            }
        }
    }

    /**
     * Responds to the model change event.
     * @param \yii\base\ModelEvent $event event instance.
     */
    public function onModelEvent($event)
    {
        if ($event instanceof AfterSaveEvent) {
            if ($this->checkChangedAttributes && empty($event->changedAttributes)) {
                return;
            }
        }

        $this->afterModelChange($event);
    }

    /**
     * This method is called after of the [[modelEvents]] events occurs at one of [[modelClasses]].
     * You should override this method inside the trait owner class, providing logic, which handles model changing.
     * You can use [[\yii\base\ModelEvent::$sender]] to get related model instance from event.
     * @param \yii\base\ModelEvent $event model event instance.
     * @throws NotSupportedException if not overridden.
     */
    protected function afterModelChange($event)
    {
        throw new NotSupportedException('Method "' . get_class($this) . '::afterModelChange()" should be implemented.');
    }
}