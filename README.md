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

Configure the console application of your yii2 installation:

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
 *
 * The user access behavior to control authorization rules
 *
 */
class Access extends Behavior
{
    public function isAllowed($operation, $relation = NULL,$user = NULL)
    {
        if(is_array($operation)) {
            foreach($operation as $op_val) {
                if($this->isAllowed($op_val, $user, $relation)) return true;
            }
            return false;
        }

        if(is_array($relation)) {
            foreach($relation as $rel_val) {
                if($this->isAllowed($operation, $user, $rel_val)) return true;
            }
            return false;
        }

        if($user === NULL) {
            $user = Yii::$app->user->identity;
            if($user === NULL) return false;
        }

        if($relation === NULL) {
            $relation = $this->getRelationName($user);
        }

        $result = $user->can($operation,['related' => $this->owner, 'relation' => $relation]);

        return $result; // Just bool expression to be returned
    }

}
```

Configure your project:

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

Create tables in your database:

```sql
CREATE TABLE qm_queues (
    id integer NOT NULL,
    tag character varying(15) NOT NULL,
    name character varying(256) NOT NULL,
    description character varying,
    scheduler character varying(50),
    options character varying,
    tasks_per_shot integer DEFAULT 1 NOT NULL,
    pid integer
);


ALTER TABLE vek.qm_queues OWNER TO inettaxi;
CREATE SEQUENCE qm_queues_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE qm_tasks (
    id integer NOT NULL,
    time_created timestamp with time zone,
    time_start timestamp with time zone,
    priority integer DEFAULT 100,
    queue_id integer,
    route character varying,
    params character varying
);

CREATE SEQUENCE qm_tasks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

ALTER TABLE ONLY qm_queues ALTER COLUMN id SET DEFAULT nextval('qm_queues_id_seq'::regclass);

ALTER TABLE ONLY qm_tasks ALTER COLUMN id SET DEFAULT nextval('qm_tasks_id_seq'::regclass);

ALTER TABLE ONLY qm_queues
    ADD CONSTRAINT qm_queues_name_key UNIQUE (name);

ALTER TABLE ONLY qm_queues
    ADD CONSTRAINT qm_queues_pkey PRIMARY KEY (id);

ALTER TABLE ONLY qm_queues
    ADD CONSTRAINT qm_queues_tag_key UNIQUE (tag);

ALTER TABLE ONLY qm_tasks
    ADD CONSTRAINT qm_tasks_pkey PRIMARY KEY (id);

ALTER TABLE ONLY qm_tasks
    ADD CONSTRAINT qm_tasks_queue_id_fkey FOREIGN KEY (queue_id) REFERENCES qm_queues(id) ON UPDATE CASCADE ON DELETE CASCADE;

```

Add your handler to console\controllers:

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
     */
    public function actionHandler($msg = '')
    {
        echo "I do something here\n$msg\n";
        return true;
    }
}

```

Go to http://your.project/queue/queue/index and create the desired queues

Use queue component in your project to add new tasks to the queues:

```php
<?php
    Yii::$app->queue->queues['my_queue_name']->add('/cons/handler',['Test message']);
```

