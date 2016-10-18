<?php

namespace queue\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

use queue\QueueManager;
use queue\models\QmQueues;

/**
 * QueueManager console controller for handling cron events.
 */
class QueueController extends Controller
{

    public $defaultAction = 'handle';

    /**
     * Количество обрабатываемых запросов
     *
     * @var int|null
     */
    public $taskPerShoot = null;

    /**
     * Оффсет
     *
     * @var int|null
     */
    public $offset = null;

    /**
     * Время между выполнениями
     *
     * @var int
     */
    public $sleep = 5;

    /**
     * Консольные опции
     *
     * @param string $actionID - действие
     *
     * @return array
     */
    public function options($actionID)
    {
        return ArrayHelper::merge(parent::options($actionID), ['taskPerShoot', 'offset', 'sleep']);
    }

    /**
     * Check for started instance of current command and create lock-file
     *
     * @return bool
     *
     * @throws \yii\base\InvalidParamException
     */
    protected function isLocked()
    {
        $lockFile = Yii::getAlias(QueueManager::getInstance()->lockFile);

        if (file_exists($lockFile)) {
            $lockingPID = trim(file_get_contents($lockFile));
            if (posix_kill($lockingPID, 0)) {
                return true;
            }

            // Lock-file is stale, so kill it.  Then move on to re-creating it.
            unlink($lockFile);
        }

        file_put_contents($lockFile, getmypid() . "\n");

        return false;
    }

    /**
     * Check if my lock-file is still alive and contains my pid
     *
     * @return bool if lock file is valid then true
     *
     * @throws \yii\base\InvalidParamException
     */
    protected function isLockAlive()
    {
        $lockFile = Yii::getAlias(QueueManager::getInstance()->lockFile);

        if (file_exists($lockFile)) {
            $lockingPID = (int)trim(file_get_contents($lockFile));
            if ($lockingPID === getmypid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handler for all queued events
     *
     * @param null|integer $queueId - id "очереди"
     *
     * @return mixed
     *
     * @throws \yii\base\InvalidParamException
     */
    public function actionHandle($queueId = null)
    {
        if ($queueId === null) {
            // Check if the instance of current command has been already started and alive
            if ($this->isLocked()) {
                return;
            }


            // Infinite cicle
            do {
                $queues = QmQueues::findQueues();
                foreach ($queues as $queue) {
                    if ($queue->pid === null || !posix_kill($queue->pid, 0)) {
                        if ($queue->scheduler && file_exists(Yii::getAlias($queue->scheduler))) {
                            $command = Yii::getAlias($queue->scheduler) . ' queue/queue/handle';
                        } else {
                            $command = Yii::$app->request->scriptFile . ' queue/queue/handle';
                        }

                        $prepare = 'nice -n 19 ' . $command . ' ' . (string)$queue->id;

                        /* Задачь за 1 выполнеие */
                        if ($this->taskPerShoot !== null) {
                            $prepare .= ' --taskPerShoot=' . $this->taskPerShoot;
                        }

                        /* Отступ */
                        if ($this->offset !== null) {
                            $prepare .= ' --offset=' . $this->offset;
                        }

                        shell_exec($prepare . ' 2>&1 &');
                    }
                }

                sleep($this->sleep);

            } while ($this->isLockAlive());

        } else {
            /* @var null|QmQueues $queue - очередь */
            $queue = QmQueues::findOne(['id' => $queueId]);
            if ($queue === null) {
                return false;
            }

            if ($queue->pid === null || !posix_kill($queue->pid, 0)) {
                $queue->pid = posix_getpid();
                if ($queue->save()) {
                    /* Устанавливаем альтернативное количество обрабатываемых строк за "выстрел" */
                    if ($this->taskPerShoot !== null) {
                        $queue->tasks_per_shot = $this->taskPerShoot;
                    }

                    if ($this->offset !== null) {
                        $queue->offset = $this->offset;
                    }

                    $queue->handleShot();

                    $queue->pid = null;
                    $queue->save();
                }
            }
        }

        return true;
    }

    /**
     * PID INFO
     */
    /*
    private function takePidInfo( $pid, $ps_opt="aux" )
    {
        $ps = shell_exec("ps ".$ps_opt."p ".$pid);
        $ps = explode("\n", $ps);

        if(count($ps)<2) {
            trigger_error("PID ".$pid." doesn't exists", E_USER_WARNING);
            return false;
        }

        foreach($ps as $key => $val) {
           $ps[$key]=explode(" ", ereg_replace(" +", " ", trim($ps[$key])));
        }

        foreach($ps[0] as $key => $val) {
            $pidinfo[$val] = $ps[1][$key];
            unset($ps[1][$key]);
        }

        if(is_array($ps[1])) {
            $pidinfo[$val].=" ".implode(" ", $ps[1]);
        } 
        return $pidinfo;
    }
    */

}
