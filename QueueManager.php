<?php

namespace queue;

use Yii;

class QueueManager extends \yii\base\Module
{
    public $controllerNamespace = 'queue\controllers';

    public $db = 'db';

    public $accessClass;

    /**
     * Number of tasks to be handled at one shot
     */
//    public $tasksAtOnce;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'queue\commands';
        }

    }

    /**
     * Initialization of the i18n translation module
     */
    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['queue'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@queue/messages',

            'fileMap' => [
                'queue' => 'queue.php',
            ],

        ];
    }
}
