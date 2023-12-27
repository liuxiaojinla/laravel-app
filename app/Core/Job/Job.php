<?php

namespace App\Core\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        try {
            $this->execute();
        } catch (\Throwable $throwable) {
            Log::error(static::class . ":error", [
                $throwable->getMessage(),
                $throwable->getTraceAsString(),
            ]);
            throw $throwable;
        }
    }

    /**
     * 执行业务逻辑
     * @return mixed
     */
    abstract protected function execute(): mixed;
}
