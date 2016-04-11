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
     * Check for started instance of current command and create lock-file
     *
     * @return bool
     */
    protected function isLocked()
    {
//        $lock_file = Yii::getAlias('@root').'/runtime/queue-manager.lock';

        $lock_file = Yii::getAlias(QueueManager::getInstance()->lockFile);

        if( file_exists( $lock_file ) ) {
            $lockingPID = trim( file_get_contents($lock_file) );
            if(posix_kill($lockingPID, 0)) return true;

            // Lock-file is stale, so kill it.  Then move on to re-creating it.
            unlink($lock_file);
        }

        file_put_contents($lock_file, getmypid() . "\n" );
        return false;
    }

    /**
     * Handler for all queued events
     * @return mixed
     */
    public function actionHandle( $id = null )
    {
        if( is_null($id) ) {
            // check if the instance of current command has been already started and alive
            if( $this->isLocked() ) return;


            // infinite cicle
            do {
            
                $queues = QmQueues::findQueues();
                foreach($queues as $tag => $queue) {
                    
                    if( is_null( $queue->pid ) || !posix_kill( $queue->pid, 0 ) ) {
                        
                        $command = Yii::$app->request->scriptFile . ' queue/queue/handle';
                        shell_exec( 'nice -n 19 '.$command.' '.strval( $queue->id ).' > /dev/null 2>&1 &' );
                        
                    }
                }
                
                sleep( 5 );
            
            } while( true );
            
        } else {
            
            $queue = QmQueues::findOne(['id' => $id]);
            if( empty($queue) ) return false;
            
            if( is_null($queue->pid) || !posix_kill( $queue->pid, 0 ) ) {
                
                $queue->pid = posix_getpid();
                if( $queue->save() ) {
                    
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
