<?php

namespace queue\commands;

use Yii;

use yii\console\Controller;

use queue\models\QmQueues;
use queue\models\QmTasks;

/**
 * TestController for QueueManager
 */
class ConsController extends Controller
{

    /**
     * Test Handler for QueueManager
     */
    public function actionHandler()
    {
        echo "I do something here";
        return true;
    }
}
