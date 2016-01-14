<?php

namespace queue\models;

use Yii;

/**
 * This is the model class for table "{{%qm_queues}}".
 *
 * @property integer $id
 * @property string $tag
 * @property string $name
 * @property string $description
 * @property string $scheduler
 * @property string $options
 *
 * @property QmTasks[] $qmTasks
 */
class QmQueues extends \yii\db\ActiveRecord
{
    private static $_queues;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qm_queues}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag', 'name'], 'required'],
            [['description', 'options'], 'string'],
            [['tag'], 'string', 'max' => 15],
            [['name'], 'string', 'max' => 256],
            [['scheduler'], 'string', 'max' => 50],
            [['name'], 'unique'],
            [['tag'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('queue', 'ID'),
            'tag' => Yii::t('queue', 'Tag'),
            'name' => Yii::t('queue', 'Name'),
            'description' => Yii::t('queue', 'Description'),
            'scheduler' => Yii::t('queue', 'Scheduler'),
            'options' => Yii::t('queue', 'Options'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findQueues()
    {
        if(!static::_queues) {
            static::_queues = static::find()->indexBy('tag')->all();
        }
        return static::_queues;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQmTasks()
    {
        return $this->hasMany(QmTasks::className(), ['queue_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQmScheduledTasks($number)
    {
        $now = (new \DateTime())->format('Y-m-d H:i:sP');
        return $this->getQmTasks()
            ->where(['>','time_start',$now])
            ->orderBy(['priority','id'])
            ->limit($this->tasks_per_shot)
            ->all();
    }

    /**
     * Add new task into the queue
     *
     * @param string|array $route Route to call for task
     * @param array $params Parameters to send to the route
     * @param array $extra Extra parameters for the task
     *
     * @return boolean Is the request successfully preformed
     */
    public function add($route,$params = [],$extra = [])
    {
        $task = new QmTask(['route' => $route, 'params' => $params, 'queue_id' => $this->id] + $extra);
        return $task->save();
    }

    /**
     * Add new task into the queue
     *
     * @param string|array $route Route to call for task
     * @param array $params Parameters to send to the route
     *
     * @return boolean Is the request successfully preformed
     */
    public function handleShot()
    {
        $tasks = $this->getQmScheduledTasks();

        foreach($tasks as $task) {
            if($task->handle) {
                $task->delete();
            }
        }
    }
}
