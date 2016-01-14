<?php

namespace queue\commands;

use Yii;
use queue\models\QmQueues;
use yii\data\ActiveDataProvider;
use yii\console\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use queue\models\QmQueue;

/**
 * QueueController implements the CRUD actions for QmQueues model.
 */
class QueueController extends Controller
{
    /**
     * Handles all queued events
     * @return mixed
     */
    public function actionHandle
    {
        $queues = QmQueue::findQueues();

        foreach($queues as $tag => $queue) {
            $queue->handleShot();
        }

        return true;
    }

}
