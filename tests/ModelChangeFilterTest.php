<?php

namespace yii2tech\tests\unit\modelchange;

use Yii;
use yii\base\Event;
use yii\base\Action;
use yii2tech\modelchange\ActionEvent;
use yii2tech\modelchange\ModelChangeFilter;
use yii2tech\tests\unit\modelchange\data\Controller;
use yii2tech\tests\unit\modelchange\data\Item;

class ModelChangeFilterTest extends TestCase
{
    protected function tearDown()
    {
        Event::offAll();
        parent::tearDown();
    }

    /**
     * @param array $config controller config.
     * @return Controller controller instance.
     */
    protected function createController($config = [])
    {
        return new Controller('test', Yii::$app, array_merge(['modelClass' => Item::className()], $config));
    }

    // Tests :

    public function testSetup()
    {
        $behavior = new ModelChangeFilter();

        $modelClasses = [
            'app\models\Item',
            'app\models\User',
        ];
        $behavior->setModelClasses($modelClasses);
        $this->assertEquals($modelClasses, $behavior->getModelClasses());
    }

    /**
     * @depends testSetup
     */
    public function testAutoDetectModelClasses()
    {
        $behavior = new ModelChangeFilter();
        $controller = $this->createController(['modelClass' => Item::className()]);
        $controller->attachBehavior('test', $behavior);

        $this->assertEquals([Item::className()], $behavior->getModelClasses());
    }

    /**
     * @depends testAutoDetectModelClasses
     */
    public function testSetupModelEventHandlers()
    {
        $behavior = new ModelChangeFilter();
        $controller = $this->createController();
        $controller->attachBehavior('test', $behavior);
        $action = new Action('test', $controller);

        $controller->beforeAction($action);
        $this->assertTrue(Event::hasHandlers(Item::className(), Item::EVENT_AFTER_INSERT));

        $controller->afterAction($action, '');
        $this->assertFalse(Event::hasHandlers(Item::className(), Item::EVENT_AFTER_INSERT));
    }

    /**
     * @depends testSetupModelEventHandlers
     */
    public function testTriggerModelChangeEvent()
    {
        $behavior = new ModelChangeFilter();
        $controller = $this->createController();
        $controller->attachBehavior('test', $behavior);

        $output = null;
        $controller->on(ModelChangeFilter::EVENT_AFTER_MODEL_CHANGE, function($event) use (&$output) {
            $output = $event;
        });

        $controller->beforeAction(new Action('test', $controller));

        $model = new Item();
        $model->name = 'some';
        $model->save(false);

        $this->assertTrue($output instanceof ActionEvent);
        $this->assertSame($model, $output->model);
    }
}