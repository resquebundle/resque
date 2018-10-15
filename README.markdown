[![Build Status](https://travis-ci.org/resquebundle/resque.svg?branch=master)](https://travis-ci.org/resquebundle/resque)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/resquebundle/resque/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/resquebundle/resque/?branch=master)

**This project IS under ACTIVE development (April 2018)**

# ResqueBundle

This is a fork of the BCCResqueBundle as ***that** bundle is no longer being activly maintained. There are a lot of outstanding issues, pull requests and bugs that need to be fixed in that project, with no activity, so we forked it, and will activly support and develop the code further in this repo. 

This is also a rebrand of Mpclarkson\ResqueBundle to place the code under a GitHub Organisation for future proof distributed development

**Contributions are welcome**

The resque bundle provides integration of [php-resque](https://github.com/chrisboulton/php-resque/) to Symfony2-3. 
It is inspired from resque, a Redis-backed Ruby library for creating background jobs, placing them on multiple queues, and processing them later.

## Features:

- Creating a Job, with container access in order to leverage your Symfony services
- Enqueue a Job with parameters on a given queue
- Creating background worker on a given queue
- An interface to monitor your queues, workers and job statuses
- Schedule jobs to run at a specific time or after a number of seconds delay
- Auto re-queue failed jobs, with back-off strategies

## Todos:

- [x] PSR4
- [x] Update admin to Bootstrap 3
- [x] Migration from BCC notes
- [x] Travis CI
- [x] Symfony 3 compatibility
- [ ] Implement Full Unit Tests
- [ ] Make decision to support PHP 7+ ONLY ;-)
- [ ] Code quality - Scrutinizer 9.5+
- [ ] Replicate functionality of the resque-web ruby lib (i.e .retry and delete failed jobs etc)
- [ ] Community contributions / Ignored PRs
- [ ] Fix bugs
- [ ] Log management
- [ ] Job status tracking
- [ ] Redis configuration
- [ ] Localisation

## Migrating from BCCResqueBundle:

Here are some notes to make it easier to migrate from the BCCResqueBundle:

- Find and replace all instances of `BCC\ResqueBundle` with `ResqueBundle\Resque` throughout your app (e.g. use statements)
- Update your `routing.yml` by replacing `@BCCResque` with `@ResqueBundle`
- The `bcc:` prefix for all commands has been dropped
- Stop and restart all workers
- The container service definition`bcc_resque.resque` has been replaced with `resque`. You can either search and replace this or create an alias as follows:
```yaml
 bcc_resque.resque:
      alias: resque
      lazy: true
```

## Migrating from Mpclarkson\ResqueBundle:

- Replace in composer with 
```
composer remove mpclarkson/resque-bundle
composer require resquebundle/resque
```
- replace all `Mpclarkson\ResqueBundle` with `ResqueBundle\Resque`
- Ensure AppKernel.php loads `new ResqueBundle\Resque\ResqueBundle(),`


## Installation and configuration:

### Requirements

Make sure you have redis installed on your machine: http://redis.io/

### Get the bundle

Add `resquebundle/resque` to your dependencies:

``` json
{
    "require": {
        ...
        "resquebundle/resque": "dev-master"
    }
    ...
}
```

To install, run `php composer.phar [update|install]`.

### Add ResqueBundle to your application kernel

``` php
<?php

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new ResqueBundle\Resque\ResqueBundle(),
            // ...
        );
    }
```

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
    class: ResqueBundle\Resque\Resque    # the resque class if different from default
    vendor_dir: "%kernel.root_dir%/../vendor"  # the vendor dir if different from default
    app_include: /pathto/bootstrap.php.cache # app include file if different from default (i.e. /var/bootstrap.php.cache)
    prefix: my-resque-prefix                 # optional prefix to separate Resque data per site/app
    redis:
        host: localhost                      # the redis host
        port: 6379                           # the redis port
        database: 1                          # the redis database
    auto_retry: [0, 10, 60]                  # auto retry failed jobs
    worker:
        root_dir: path/to/worker/root        # the root dir of app that run workers (optional)
```

See the [Auto retry](#auto-retry) section for more on how to use `auto_retry`.

Set `worker: root_dir:` in case job fails to run when worker systems are hosted on separate server/dir from the system creating the queue.
When running multiple configured apps for multiple workers, all apps must be able to access by the same root_dir defined in `worker: root_dir`.

### Optional, configure lazy loading

This bundle is prepared for lazy loading in order to make a connection to redis only when its really used. Symfony2 supports that starting with 2.3. To make it work an additional step needs to be done. You need to install a proxy manager to your Symfony2 project. The full documentation for adding the proxy manager can be found in [Symfony2's Lazy Service documentation](http://symfony.com/doc/current/components/dependency_injection/lazy_services.html).

## Creating a Job

A job is a subclass of the `ResqueBundle\Resque\Job` class. You also can use the `Mpclarkson\Resque\ContainerAwareJob` if you need to leverage the container during job execution.
You will be forced to implement the run method that will contain your job logic:

``` php
<?php

namespace My;

use ResqueBundle\Resque\Job;

class MyJob extends Job
{
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

// get resque (only if service has been made public - else using DI)
$resque = $this->get('ResqueBundle\Resque\Resque');

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

// get resque (only if service has been made public - else using DI)
$resque = $this->get('ResqueBundle\Resque\Resque');

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

### Access the container from inside your job

Just extend the `ContainerAwareJob`:

``` php
<?php

namespace My;

use ResqueBundle\Resque\ContainerAwareJob;

class MyJob extends ContainerAwareJob
{
    public function run($args)
    {
        $doctrine = $this->getContainer()->getDoctrine();
        ...
    }
}
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
