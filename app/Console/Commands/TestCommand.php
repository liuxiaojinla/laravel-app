<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试命令';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        $task = LiveCaptureTask::query()->where('id', 1)->first();
//        StartLiveCaptureJob::dispatch($task);


        $i = 0;
        while ($i < 100) {
            sleep(1);
            $i++;
        }

        return 0;
    }
}
