[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/resquebundle/resque/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/resquebundle/resque/?branch=master)

**This project IS NO LONGER UNDER ACTIVE development (last major update September 2021)**

If you would like to take over maintenance of this project please contact phil@phil-taylor.com - I no longer use the code here in any live project as I moved to Symfony Messenger for my queue needs. 

If you are using PHP 8 plesae see the `php8` branch for the latest stable release

# ResqueBundle

## Compatibiltiy

 - For Symfony 5+ please use ResqueBundle v4.0.0+
 - For Symfony 4+ please use major series ResqueBundle v3+ and work towards Symfony 5 migration ;-)
 - For Symfony 3+ please peg to exact release ResqueBundle v2.0.9+ and think about your decision to even use Symfony 3 ;-)

Note that we dont offer the same b/c promise as symfony itself, but try our hardest to make major versions for major symfony versions.

## Update May 2020

 - Inject ParameterBagInterface instead of directly accessing container in commands and controller
 - User kernel.project_dir instead of kernel.root_dir (b/c break!, you need to update your config yml)
 - Update Routing controller to use long syntax
 - Use `@Bundle` syntax for loading twig templates
 - force a minimum of Symfony 4.1.2 for critical security
 - Use correct Process function for max compatibility
 - Drop Symfony 3.4 support totally, sorry. 

## Update November 2019

I have now worked on the master branch to implement compatibility with Symfony 4+, using Dependancy injection instead of `ContainerAwareJob`.

If you are still using Symfony 3 then you MUST peg your composer.json to release 2.0.9

The first version of this bundle that is highly compatible with, and activly maintained, is 3.0.0

If you have used this before, and want to get up to date, then you need to 
 - upgrade to 3.0.0+ version of this bundle
 - use Symfony 4 (im using 4.4.0RC1 at the moment)
 - change your Jobs to extend `ResqueBundle\Resque\Job` and not `ContainerAwareJob`
 - add `__construct` methods to inject your depenancies
 - remove ALL REFERENCES to the container or `getContainer` from your jobs
 - Enjoy!

# ResqueBundle History

This is a fork of the BCCResqueBundle as ***that** bundle is no longer being actively maintained. There are a lot of outstanding issues, pull requests and bugs that need to be fixed in that project, with no activity, so we forked it, and will activly support and develop the code further in this repo. 

This is also a rebrand of Mpclarkson\ResqueBundle to place the code under a GitHub Organisation for future proof distributed development

**Contributions are welcome**

The resque bundle provides integration of [php-resque](https://github.com/chrisboulton/php-resque/) to Symfony4. 
It is inspired from resque, a Redis-backed Ruby library for creating background jobs, placing them on multiple queues, and processing them later.

## Features:

- Creating a Job, with container access in order to leverage your Symfony services
- Enqueue a Job with parameters on a given queue
- Creating background worker on a given queue
- An interface to monitor your queues, workers and job statuses
- Schedule jobs to run at a specific time or after a number of seconds delay
- Auto re-queue failed jobs, with back-off strategies
- Dependency Injection to Jobs

## Installation and configuration:

### Requirements

Symfony 4+

### Get the bundle

To install, run `composer req resquebundle/resque`

### Import the routing configuration

Add to the following to `routing.yml`:

``` yml
# app/config/routing.yml
ResqueBundle:
    resource: "@ResqueBundle/Resources/config/routing.xml"
    prefix:   /resque
```

You can customize the prefix as you wish.

You can now access the dashboard at this url: `/resque`

To secure the dashboard, you can add the following to your `security.yml`, assuming your administrator role is `ROLE_ADMIN`

```yml
access_control:
  - { path: ^/resque, roles: ROLE_ADMIN }
```

Now only users with the role ROLE_ADMIN will be able to access the dashboard at this url: `/resque`

### Optional, set configuration

You may want to add some configuration to your `config.yml`

``` yml
# app/config/config.yml
resque:
    app_include: /pathto/bootstrap.php.cache # app include file if different from default (i.e. /var/bootstrap.php.cache)
    prefix: my-resque-prefix                 # optional prefix to separate Resque data per site/app
    redis:
        host: localhost                      # the redis host
        port: 6379                           # the redis port
        database: 1                          # the redis database
        password: ~                          # the redis password, defaults to null
    auto_retry: [0, 10, 60]                  # auto retry failed jobs
    worker:
        project_dir: path/to/worker/project_dir        # the project_dir of app that run workers (optional)
```

See the [Auto retry](#auto-retry) section for more on how to use `auto_retry`.

Set `worker: project_dir:` in case job fails to run when worker systems are hosted on separate server/dir from the system creating the queue.
When running multiple configured apps for multiple workers, all apps must be able to access by the same root_dir defined in `worker: root_dir`.

## Creating a Job

A job is a subclass of the `ResqueBundle\Resque\Job` class. 

You will be forced to implement the run method that will contain your job logic:

``` php
<?php

namespace My;

use ResqueBundle\Resque\Job;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

class MyJob extends Job
{
    /**
     * @var string The queue name
     */
    public $queue = 'myqueue';

    /**
     * @var ManagerRegistry
     */
    private $registry;
    
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * Use the __construct to inject your dependencies
     *
     * @param array           $args
     * @param ManagerRegistry $registry
     */
    public function __construct(
        $args = [],
        ManagerRegistry $registry
    ) {
        $this->registry      = $registry;
        $this->em            = $registry->getManager();
        parent::__construct($args);
    }

    public function run($args)
    {
        file_put_contents($args['file'], $args['content']);
    }
}
```

As you can see you get an $args parameter that is the array of arguments of your Job.

## Adding a job to a queue

You can get the resque service simply by using the container. From your controller you can do:

``` php
<?php

// get resque (only if service has been made public - else USE DI LIKE YOU SHOULD)
// $resque = $this->get('ResqueBundle\Resque\Resque');

// create your job
$job = new MyJob();
$job->args = array(
    'file'    => '/tmp/file',
    'content' => 'hello',
);

// enqueue your job
$resque->enqueue($job);
```

## Running a worker on a queue

Executing the following commands will create a work on :
- the `default` queue : `app/console resque:worker-start default`
- the `q1` and `q2` queue : `app/console resque:worker-start q1,q2` (separate name with `,`)
- all existing queues : `app/console resque:worker-start "*"`

You can also run a worker foreground by adding the `--foreground` option;

By default `VERBOSE` environment variable is set when calling php-resque
- `--verbose` option sets `VVERBOSE`
- `--quiet` disables both so no debug output is thrown

See php-resque logging option : https://github.com/chrisboulton/php-resque#logging

## Adding a delayed job to a queue

You can specify that a job is run at a specific time or after a specific delay (in seconds).

From your controller you can do:

``` php
<?php

// get resque (only if service has been made public - else use DI LIKE YOU SHOULD)
//$resque = $this->get('ResqueBundle\Resque\Resque');

// create your job
$job = new MyJob();
$job->args = array(
    'file'    => '/tmp/file',
    'content' => 'hello',
);

// enqueue your job to run at a specific \DateTime or int unix timestamp
$resque->enqueueAt(\DateTime|int $at, $job);

// or

// enqueue your job to run after a number of seconds
$resque->enqueueIn($seconds, $job);

```

You must also run a `scheduledworker`, which is responsible for taking items out of the special delayed queue and putting
them into the originally specified queue.

`app/console resque:scheduledworker-start`

Stop it later with `app/console resque:scheduledworker-stop`.

Note that when run in background mode it creates a PID file in 'cache/<environment>/resque_scheduledworker.pid'. If you
clear your cache while the scheduledworker is running you won't be able to stop it with the `scheduledworker-stop` command.

Alternatively, you can run the scheduledworker in the foreground with the `--foreground` option.

Note also you should only ever have one scheduledworker running, and if the PID file already exists you will have to use
the `--force` option to start a scheduledworker.

## Manage production workers with supervisord

It's probably best to use supervisord (http://supervisord.org) to run the workers in production, rather than re-invent job
spawning, monitoring, stopping and restarting.

Here's a sample conf file

```ini
[program:myapp_phpresque_default]
command = /usr/bin/php /home/sites/myapp/bin/console resque:worker-start high --env=prod --foreground --verbose
user = myusername
stopsignal=QUIT

[program:myapp_phpresque_scheduledworker]
command = /usr/bin/php /home/sites/myapp/prod/bin/console resque:scheduledworker-start --env=prod --foreground --verbose
user = myusername
stopsignal=QUIT

[group:myapp]
programs=myapp_phpresque_default,myapp_phpresque_scheduledworker
```

(If you use a custom Resque prefix, add an extra environment variable: PREFIX='my-resque-prefix')

Then in Capifony you can do

`sudo supervisorctl stop myapp:*` before deploying your app and `sudo supervisorctl start myapp:*` afterwards.

## More features

### Changing the queue

You can change a job queue just by setting the `queue` field of the job:

From within the job:

``` php
<?php

namespace My;

use ResqueBundle\Resque\Job;

class MyJob extends Job
{
    public function __construct()
    {
        $this->queue = 'my_queue';
    }

    public function run($args)
    {
        ...
    }
}
```

Or from outside the job:

``` php
<?php

// create your job
$job = new MyJob();
$job->queue = 'my_queue';
```


### Stop a worker

Use the `app/console resque:worker-stop` command.

- No argument will display running workers that you can stop.
- Add a worker id to stop it: `app/console resque:worker-stop ubuntu:3949:default`
- Add the `--all` option to stop all the workers.


### Auto retry

You can have the bundle auto retry failed jobs by adding `retry strategy` for either a specific job, or for all jobs in general:

The following will allow `Some\Job` to retry 3 times.

* right away
* after a 10 second delay
* after a 60 second delay

```yml
resque:
    redis:
        ....
    auto_retry:
        Some\Job: [0, 10, 60]
```

Setting strategy for all jobs:

```yml
resque:
    auto_retry: [0, 10, 60]
```

With default strategy for all but specific jobs:

```yml
resque:
    auto_retry:
    	default:        [0, 10, 60]
        Some\Job:       [0, 10, 120, 240]
        Some\Other\Job: [10, 30, 120, 600]
```

The `default` strategy (if provided) will be applied to all jobs that does not have a specific strategy attached. If not provided these jobs will not have auto retry.

You can disable `auto_retry` for selected jobs by using an empty array:

```yml
resque:
    auto_retry:
    	default:        [0, 10, 60]
        Some\Job:       []
        Some\Other\Job: [10, 30, 120, 600]
```

Here `Some\Job` will not have any `auto_retry` attached.

**Please note**

To use the `auto_retry` feature, you must also run the scheduler job:

`app/console resque:scheduledworker-start`
