<?php

namespace App\Extensions\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Symfony\Component\Console\Input\ArgvInput;
use Xin\Robot\RobotManager;

class RobotHandler extends AbstractProcessingHandler
{
    /**
     * @var string
     */
    protected $config = [
        'robot' => 'danger',
    ];

    /**
     * RobotHandler constructor.
     *
     * @param array $config
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(array $config = [], $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->config = array_replace_recursive($this->config, $config);
    }

    /**
     * @param LogRecord $record
     */
    protected function write(LogRecord $record): void
    {
        $env = app()->environment();
        if ($env === 'local') {
            return;
        }

        if (!$this->allowSend($count)) {
            return;
        }

        $message = $this->buildContents($record, $env, $count);

        $robot = $record['context']['robot'] ?? null;
        $this->sendRobotMessage($message, $robot);
    }

    /**
     * 机器人发送告警消息
     * @param string $contents
     * @return void
     */
    protected function sendRobotMessage($contents, $useRobot = null)
    {
        $useRobot = $useRobot ?: $this->robot();
        $useRobot = $useRobot === true ? null : $useRobot;
        try {
            /** @var RobotManager $factory */
            $factory = app('robot');
            $result = $factory->robot($useRobot)->sendMarkdownMessage($contents);
        } catch (\Throwable $e) {
        }

    }

    /**
     * @param LogRecord $record
     * @param string $env
     * @param int $errCount
     * @return string
     */
    protected function buildContents(LogRecord $record, $env, $errCount)
    {
        $title = $record['level_name'];
        $message = $record['formatted'];
        $message = substr($message, 0, 1024);
        $ip = request()->server('SERVER_ADDR');

        if (app()->runningInConsole()) {
            $input = new ArgvInput();
            $url = $input->getFirstArgument() .
                "->args:" . json_encode($input->getArguments()) .
                ":options:" . json_encode($input->getOptions());
        } else {
            $url = request()->fullUrl();
            $url = request()->method() . " " . $url;
        }

        return <<<MARKDOWN
<font color="warning">**{$title}({$env}:$ip:10 分钟出现 {$errCount} 次)**</font>
<font color="info">{$url}</font>
<font color="comment">{$message}</font>
MARKDOWN;
    }

    /**
     * 自动降级发送
     *
     * @param int $count
     * @return bool
     */
    private function allowSend(&$count = 0)
    {
        try {
            $count = $this->resolveErrorCount();
        } catch (\Exception $e) {
            return false;
        }

        $levels = [10, 100, 1000, 10000];
        foreach ($levels as $level) {
            if ($count === 1 || $count % $level === 0) {
                return true;
            }
        }
        $maxLevel = $levels[count($levels) - 1];

        return $count % $maxLevel === 0;
    }

    /**
     * 生成缓存key
     *
     * @return string
     */
    private function resolveErrorCacheKey()
    {
        $url = request()->fullUrl();
        $url = preg_replace('/\&timestamp=\d{0,10}/', '', $url);
        return "err_c_" . md5($url);
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function resolveErrorCount()
    {
        $key = $this->resolveErrorCacheKey();
        $count = cache($key);

        if ($count === null) {
            $count = 1;
            cache()->put($key, 1, now()->addMinutes(10));
        } else {
            $count++;
            cache()->increment($key);
        }

        return $count;
    }

    /**
     * 获取机器人Key
     *
     * @return string
     */
    protected function robot()
    {
        if (isset($this->config['robot']) && $this->config['robot']) {
            return $this->config['robot'];
        }

        return null;
    }
}
