<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('queue', 'Qm Queues');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qm-queues-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('queue', 'Create Qm Queues'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'route',
            // 'options',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
