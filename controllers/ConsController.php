<?php

namespace queue\controllers;

use Yii;

use yii\console\Controller;

use queue\models\QmQueues;
use queue\models\QmTasks;

/**
 * QueueController implements the CRUD actions for QmQueues model.
 */
class ConsController extends Controller
{
    public function actionHandler($id)
    {
        echo "I do something here";
        return true;
    }
}
