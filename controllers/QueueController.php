<?php

namespace queue\controllers;

use Yii;

use yii\data\ActiveDataProvider;
use yii\web\Controller;

use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use queue\models\QmQueues;

/**
 * QueueController implements the CRUD actions for QmQueues model.
 */
class QueueController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // everything else is denied
                ],
            ],
        ];
    }

    /**
     * Lists all QmQueues models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->checkAccess('queue.queue.index');

        $dataProvider = new ActiveDataProvider([
            'query' => QmQueues::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QmQueues model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess('queue.queue.view', $model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new QmQueues model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QmQueues();
        $this->checkAccess('queue.queue.create', $model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QmQueues model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess('queue.queue.update',$model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing QmQueues model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess('queue.queue.delete',$model);

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the QmQueues model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QmQueues the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QmQueues::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * check access rights for current user
     */
    protected function checkAccess( $rightName, $access = null )
    {
        if( is_null($access) ) {
            $class = \queue\QueueManager::getInstance()->accessClass;
            $access = new $class;
        }

        if( !$access->isAllowed($rightName)) {
            throw new ForbiddenHttpException( Yii::t('wallets','Access restricted') );
        }
    }
}
