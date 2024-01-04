<?php

namespace App\Core\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 失败重试次数
     * @var int
     */
    protected $retryTimes = 1;

    /**
     * 失败重试间隔
     * @var int
     */
    protected $retrySleepMilliseconds = 0;

    /**
     * @var OutputStyle
     */
    protected OutputStyle $output;


    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->initOutputStyle();

        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        $this->output->info(static::class . " begin ...");
        try {
            $this->execute();
            $this->output->success(static::class . " end.");
        } catch (\Throwable $e) {
            $this->output->error(static::class . " error:" . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }

//        $currentAttempts = 0;
//
//        try {
//            $this->output->info(static::class . " begin, max retry times " . $this->retryTimes . " ...");
//            retry(
//                $this->retryTimes,
//                function ($attempts) use (&$currentAttempts) {
//                    $currentAttempts = $attempts;
//                    $this->output->info(static::class . "try execute " . $attempts . ".");
//                    $this->execute();
//                },
//                method_exists($this, 'retrySleepMilliseconds') ? [$this, 'retrySleepMilliseconds'] : $this->retrySleepMilliseconds,
//                function (\Throwable $e) use ($currentAttempts) {
//                    $this->output->error(static::class . "try execute " . $currentAttempts . " error:" . $e->getMessage() . "\n" . $e->getTraceAsString());
//                    Log::error(static::class . ":error", [
//                        $e->getMessage(),
//                        $e->getTraceAsString(),
//                    ]);
//
//                    if (method_exists($this, 'retryWhen')) {
//                        return $this->retryWhen($e);
//                    }
//
//                    return true;
//                }
//            );
//            $this->output->success(static::class . " end.");
//        } catch (\Throwable $throwable) {
//            $this->output->error(static::class . " end.");
//            throw $throwable;
//        }
    }

    /**
     * 初始化输出样式实例
     * @return void
     */
    private function initOutputStyle()
    {
        $this->output = app(OutputStyle::class, [
            'input' => new ArgvInput(),
            'output' => new ConsoleOutput(),
        ]);
    }

    /**
     * 执行业务逻辑
     * @return void
     */
    abstract protected function execute();
}
