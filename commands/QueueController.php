<?php

namespace queue\commands;

use Yii;
use queue\models\QmQueues;
use yii\data\ActiveDataProvider;
use yii\console\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        $dataProvider = new ActiveDataProvider([
            'query' => QmQueues::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

}
