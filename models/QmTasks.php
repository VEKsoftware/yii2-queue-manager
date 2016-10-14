<?php

namespace queue\models;

use Yii;
use queue\components\CommonRecord;

/**
 * This is the model class for table "{{%qm_tasks}}".
 *
 * @property integer  $id
 * @property string   $time_created
 * @property string   $time_start
 * @property integer  $priority
 * @property integer  $queue_id
 * @property string   $route
 * @property string   $params
 *
 * @property QmQueues $queue
 */
class QmTasks extends CommonRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName()
    {
        return '{{%qm_tasks}}';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules()
    {
        return [
//            [['time_start'], 'date','timestampAttribute' => 'timestamp_start'],
            [['priority', 'queue_id'], 'integer'],
            [['route', 'params'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('queue', 'ID'),
            'time_created' => Yii::t('queue', 'Time Created'),
            'time_start' => Yii::t('queue', 'Time Start'),
            'priority' => Yii::t('queue', 'Priority'),
            'queue_id' => Yii::t('queue', 'Queue'),
            'route' => Yii::t('queue', 'Route'),
            'params' => Yii::t('queue', 'Params'),
        ];
    }

    /**
     * Relation с очередью
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQueue()
    {
        return $this->hasOne(QmQueues::className(), ['id' => 'queue_id']);
    }

    /**
     * Обработка задачи
     *
     * @return int|mixed|\yii\console\Response
     */
    public function handle()
    {
        return Yii::$app->runAction($this->route, $this->params);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function afterFind()
    {
        parent::afterFind();
        if ($this->params) {
            $this->params = unserialize($this->params);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $insert - true - вставка, false - обновление
     *
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (is_array($this->params)) {
            $this->params = serialize($this->params);
        }

        return parent::beforeSave($insert);
    }
}
