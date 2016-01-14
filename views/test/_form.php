<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model queue\models\QmQueues */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="qm-queues-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('queue', 'Create'), [
            'class' => 'btn btn-success',
            'name' => 'done',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
