<?php

namespace App\Repositories\Examples;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Validation\ValidationException;

class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;
    /**
     * @var ExampleRepository
     */
    private $exampleRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws ValidationException
     */
    public function handle()
    {
        app()->call([$this, 'init']);

        $this->exampleRepository->getById($this->data['id']);
    }

    protected function init(ExampleRepository $exampleRepository)
    {
        $this->exampleRepository = $exampleRepository;
    }
}
