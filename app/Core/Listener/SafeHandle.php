<?php

namespace App\Core\Listener;

use Illuminate\Support\Facades\Log;

trait SafeHandle
{
    /**
     * @var mixed
     */
    protected $event;

    /**
     * Handle the event.
     *
     * @param mixed $event
     * @return void
     */
    public function handle($event): void
    {
        try {
            $this->event = $event;
            $this->action($event);
        } catch (\Throwable $e) {
            $this->catch($e);
            Log::error(static::class . " error", [$event, $e]);
        }
    }

    /**
     * 异常事件
     * @param \Throwable $e
     * @return void
     */
    protected function catch(\Throwable $e)
    {

    }

    abstract protected function action(mixed $event);
}
