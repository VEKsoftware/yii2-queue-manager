<?php

namespace queue;

use Yii;

class QueueManager extends \yii\base\Module
{
    public $controllerNamespace = 'queue\controllers';

    /**
     * Number of tasks to be handled at one shot
     */
//    public $tasksAtOnce;

    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'queue\commands';
        }

    }
}
