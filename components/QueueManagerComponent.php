<?php
namespace queue\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use queue\models\QmQueues;

class QueueManagerComponent extends Component
{
    public function getQueues()
    {
        return QmQueues::findQueues();
    }
}