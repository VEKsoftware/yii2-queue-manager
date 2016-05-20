Queue Manager
=============

The module manages the tasks and queues. To store the tasks and queues it
uses local database. CRON calls only master process since it
does not allow time intervals less than 1 minute. The master process calls tasks from the queues.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist veksoftware/yii2-queue-manager "*"
```

or add

```
"veksoftware/yii2-queue-manager": "*"
```

to the require section of your `composer.json` file.


Usage
-----

To setup the module you need to go though several steps.

#### Configure the console application of your yii2 installation:

```php
...
    'modules' => [
...
        'queue' => [
            'class' => 'queue\QueueManager',
            'accessClass' => 'common\behaviors\Access', // Class for managing access rights
        ],
...
    ],
...
```

The Access class must be like this:
```php
<?php
namespace common\behaviors;

use yii;
use yii\base\Behavior;

/**
 * The user access behavior to control authorization rules
 */
class Access extends Behavior
{
    public function isAllowed($operation, $relation = null, $user = null)
    {
        if (is_array($operation)) {
            foreach ($operation as $op_val) {
                if ($this->isAllowed($op_val, $user, $relation)) return true;
            }
            return false;
        }

        if (is_array($relation)) {
            foreach ($relation as $rel_val) {
                if ($this->isAllowed($operation, $user, $rel_val)) return true;
            }
            return false;
        }

        if ($user === null) {
            $user = Yii::$app->user->identity;
            if ($user === null) return false;
        }

        if ($relation === null) {
            $relation = $this->getRelationName($user);
        }

        $result = $user->can($operation, ['related' => $this->owner, 'relation' => $relation]);

        return $result; // Just bool expression to be returned
    }

}
```

#### Configure your project:

```php
<?php
...
    'components' => [
...
        'queue' => [
            'class' => '\queue\components\QueueManagerComponent',
            'accessClass' => '\common\behaviors\Access',
        ],
...
    ],
    'modules' => [
...
        'queue' => [
            'class' => 'queue\QueueManager',
            'accessClass' => 'partneruser\behaviors\Access',
        ],
...
    ],
...

```

#### Apply migrations for create tables:
```bash
yii migrate/up --migrationPath=@app/vendor/VEKsoftware/yii2-queue-manager/migrations
```

#### Add your handler to console\controllers:

```php
    <?php
    
    namespace console\controllers;
    
    use Yii;
    
    use yii\console\Controller;
    
    /**
     * TestController for QueueManager
     */
    class ConsController extends Controller
    {
        public $defaultAction = 'handler';
    
        /**
         * Test Handler for QueueManager
         * @param string $msg
         * @return bool
         */
        public function actionHandler($msg = '')
        {
            echo "I do something here\n$msg\n";
            return true;
        }
    }
```

#### Setup your queues

Go to http://your.project/queue/queue/index and create the desired queues

#### Setup your CRON to start queue manager periodically.

For that type in your shell:
```bash
sudo crontab -u www-data -e
```
and edit the crontab file as:
```cron
SHELL=/bin/bash
*/15 * * * * cd project/directory; nohup ./yii queue/queue/handler >> runtime/cron.log &
```

#### Use queue component in your project to add new tasks to the queues:

```php
<?php
    Yii::$app->queue->queues['my_queue_name']->add('/cons/handler',['Test message']);
```

