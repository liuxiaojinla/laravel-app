<?php

namespace App\Console\Commands;

use App\Jobs\TestJob;
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
        $this->output->info("test command.");

//        for ($i = 0; $i < 2; $i++) {
//            TestJob::dispatch()->delay($i);
//        }


        return 0;
    }
}
