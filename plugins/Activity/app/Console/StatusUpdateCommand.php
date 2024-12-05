<?php

namespace Plugins\Activity\App\Console;

use Illuminate\Console\Command;
use Plugins\Activity\App\Enums\ActivityStatus;
use Plugins\Activity\App\Models\Activity;

class StatusUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'activity:status-update';

    /**
     * The console command description.
     */
    protected $description = '更新活动任务状态。';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 更新未开始的活动
        $afterRows = Activity::query()->where([
            ['start_time', '<=', now()->getTimestamp()],
            ['status', '=', ActivityStatus::WAITING],
        ])->update([
            'status' => ActivityStatus::PENDING,
        ]);
        $this->comment("未开始的活动共更新行数：{$afterRows}");

        // 更新进行中的活动
        $afterRows = Activity::query()->where([
            ['end_time', '<=', now()->getTimestamp()],
            ['status', '=', ActivityStatus::PENDING],
        ])->update([
            'status' => ActivityStatus::COMPLETED,
        ]);
        $this->comment("已开始的活动共更新行数：{$afterRows}");
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            //            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            //            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
