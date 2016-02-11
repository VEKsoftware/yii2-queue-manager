<?php

namespace queue\commands;

use Yii;
use yii\data\ActiveDataProvider;
use yii\console\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use queue\QueueManager;
use queue\models\QmQueues;

/**
 * QueueManager console controller for handling cron events.
 */
class QueueController extends Controller
{
    public $defaultAction = 'handle';

    /**
     * Handler for all queued events
     * @return mixed
     */
    public function actionHandle()
    {
        $queues = QmQueues::findQueues();
        
        $command = QueueManager::getInstance()->systemHandleQueue;
        
        foreach($queues as $tag => $queue) {
            
            if( is_null( $queue->pid ) || !posix_kill( $queue->pid, 0 ) ) {
                
                shell_exec( 'nice -n 19 '.$command.' '.strval( $queue->id ).' > /dev/null 2>&1 &' );
                
            }
        }
        
        return true;
    }
    
    /**
     * Handler for one queue
     * @return void
     */
    public function actionHandleQueue( $id )
    {
        $queue = QmQueues::findOne(['id' => $id]);
        if( empty($queue) ) return false;
        
        // save process ID or die()
        
        if( is_null($queue->pid) || !posix_kill( $queue->pid, 0 ) ) {
                
                $pid = posix_getpid();
                
                $queue->pid = $pid;
                $queue->save();

        } else {
            
            die();
            
        }
        
        // cicle of queue handler
        
        do {
            $queue->handleShot();
            sleep(5);
        } while( true );
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
