<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model queue\models\QmQueues */

$this->title = Yii::t('queue', 'Create Qm Queues');
$this->params['breadcrumbs'][] = ['label' => Yii::t('queue', 'Qm Queues'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qm-queues-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
