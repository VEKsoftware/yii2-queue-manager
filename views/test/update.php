<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model queue\models\QmQueues */

$this->title = Yii::t('queue', 'Update {modelClass}: ', [
    'modelClass' => 'Qm Queues',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('queue', 'Qm Queues'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('queue', 'Update');
?>
<div class="qm-queues-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
//        'model' => $model,
    ]) ?>

</div>
