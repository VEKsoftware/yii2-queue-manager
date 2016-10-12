<?php

namespace queue\models;

use Yii;
use queue\QueueManager;
use queue\components\CommonRecord;

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
class QmQueues extends CommonRecord
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
            [['tasks_per_shot'], 'integer'],
            [['tag'], 'unique'],
            [['pid'], 'integer'],
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
            'tasks_per_shot' => Yii::t('queue', 'Tasks Per Shot'),
            'pid' => Yii::t('queue', 'Process ID')
        ];
    }

    /**
     * @inherit
     */
    public function behaviors()
    {
        return [
            'access'=>[
                'class' => QueueManager::getInstance()->accessClass,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findQueues()
    {
        if(!static::$_queues) {
            static::$_queues = static::find()->indexBy('tag')->all();
        }
        return static::$_queues;
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
    public function getQmScheduledTasks()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:sP');

        return $this->getQmTasks()
            ->where(['or',['<','[[time_start]]',$now],['[[time_start]]' => NULL]])
            ->orderBy(['[[priority]]' => SORT_ASC,'[[id]]' => SORT_ASC])
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
        $task = new QmTasks(['route' => $route, 'params' => $params, 'queue_id' => $this->id] + $extra);
        return $task->save() ? $task->id : NULL;
    }

    /**
     * Check for task exists
     *
     * @param string $route Route to call for task
     * @param array $params Parameters to send to the route
     *
     * @return boolean Check result
     */
    public function checkTaskExists($route, $params)
    {
        return $this->getQmTasks()->where([
            'and',
            ['route' => $route],
            ['params' => serialize($params)],
            ['queue_id' => $this->id]
        ])->exists();
    }

    /**
     * Delete tasks with certain route and params
     *
     * @param string $route Route to call for task
     * @param array $params Parameters to send to the route
     */
    public function deleteTask($route, $params)
    {
        $tasks = $this->getQmTasks()->where([
            'and',
            ['route' => $route],
            ['params' => serialize($params)],
            ['queue_id' => $this->id]
        ])->all();

        if(!empty($tasks)) {
            foreach($tasks as $task) {

                $task->delete();

            }
        }
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
            if($task->handle()) {
                $task->delete();
            }
        }
    }
}
