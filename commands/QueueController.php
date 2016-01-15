<?php

namespace queue\commands;

use Yii;
use yii\data\ActiveDataProvider;
use yii\console\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use queue\models\QmQueues;

/**
 * QueueManager console controller for handling cron events.
 */
class QueueController extends Controller
{
    /**
     * Handler for all queued events
     * @return mixed
     */
    public function actionHandle()
    {
        $queues = QmQueues::findQueues();

        foreach($queues as $tag => $queue) {
            $queue->handleShot();
        }

        return true;
    }

}
