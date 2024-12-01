<?php

namespace App\Jobs;

use Xin\LaravelFortify\Queue\Job;

class TestJob extends Job
{
    protected $retryTimes = 3;

    /**
     * 任务可尝试的次数。
     *
     * @var int
     */
//    public $tries = 5;

    protected $retrySleepMilliseconds = 300;

    protected function execute()
    {
        sleep(5);
        $this->output->success("execute ok.");
        return false;
    }

//    public function retryUntil()
//    {
//        return now()->addSeconds(10);
//    }
}
