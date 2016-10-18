<?php

namespace queue\models;

use Yii;
use queue\QueueManager;
use queue\components\CommonRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%qm_queues}}".
 *
 * @property integer   $id
 * @property string    $tag
 * @property string    $name
 * @property string    $description
 * @property string    $scheduler
 * @property string    $options
 *
 * @property QmTasks[] $qmTasks
 */
class QmQueues extends CommonRecord
{
    private static $_queues;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName()
    {
        return '{{%qm_queues}}';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
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
     * {@inheritdoc}
     *
     * @return array
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
     * {@inherit}
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => QueueManager::getInstance()->accessClass,
            ],
        ];
    }

    /**
     * Поиск очередей
     *
     * @return QmQueues[]
     */
    public static function findQueues()
    {
        if (!static::$_queues) {
            static::$_queues = static::find()->indexBy('tag')->all();
        }

        return static::$_queues;
    }

    /**
     * Получаем задачи для текущей очереди
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQmTasks()
    {
        return $this->hasMany(QmTasks::className(), ['queue_id' => 'id']);
    }

    /**
     * Получаем задачи TODO надо подобрать описание
     *
     * @return QmTasks[]
     */
    public function getQmScheduledTasks()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:sP');

        return $this->getQmTasks()
            ->where(['or', ['<', '[[time_start]]', $now], ['[[time_start]]' => null]])
            ->orderBy(['[[priority]]' => SORT_ASC, '[[id]]' => SORT_ASC])
            ->limit($this->tasks_per_shot)
            ->all();
    }

    /**
     * Add new task into the queue
     *
     * @param string|array $route  Route to call for task
     * @param array        $params Parameters to send to the route
     * @param array        $extra  Extra parameters for the task
     *
     * @return boolean Is the request successfully preformed
     */
    public function add($route, $params = [], $extra = [])
    {
        $prepareTask = array_merge(
            [
                'route' => $route,
                'params' => $params,
                'queue_id' => $this->id
            ],
            $extra
        );
        $task = new QmTasks($prepareTask);

        return $task->save() ? $task->id : null;
    }

    /**
     * Check for task exists
     *
     * @param string $route  Route to call for task
     * @param array  $params Parameters to send to the route
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
     * @param string $route  Route to call for task
     * @param array  $params Parameters to send to the route
     *
     * @return void
     *
     * @throws \yii\db\Exception
     */
    public function deleteTask($route, $params)
    {
        /* Получаем задачи */
        $tasks = $this->getQmTasks()
            ->where(
                [
                    'and',
                    ['route' => $route],
                    ['params' => serialize($params)],
                    ['queue_id' => $this->id]
                ]
            )
            ->all();

        if (!empty($tasks)) {
            /* Получаем id задачь, которые нужно удалить */
            $prepareIds = array_map(
                function ($value) {
                    return $value->id;
                },
                $tasks
            );

            /* Массово удаляем задачи */
            QmTasks::getDb()
                ->createCommand(
                    'DELETE FROM ' . QmTasks::tableName() . ' WHERE id IN (' . implode(',', $prepareIds) . ')'
                )
                ->execute();
        }
    }

    /**
     * Выполняем задачи.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handleShot()
    {
        $tasks = $this->getQmScheduledTasks();

        foreach ($tasks as $task) {
            if ($task->handle()) {
                $task->delete();
            }
        }
    }
}
