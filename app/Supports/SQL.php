<?php

namespace App\Supports;

use Illuminate\Support\Facades\DB;

class SQL
{
    /**
     * @var bool
     */
    protected static bool $booted = false;

    /**
     * @var array
     */
    protected static array $sqlList = [];

    /**
     * @var int
     */
    protected static int $maxCacheSqlCount = 100;

    /**
     * @var string
     */
    protected static string $lastSql;

    /**
     * @var bool
     */
    protected static bool $enableDump = false;

    /**
     * @var bool
     */
    protected static bool $forceEnableDump = false;

    /**
     * 启动SQL监听器
     */
    protected static function boot(): void
    {
        DB::listen(static function ($query) {
            $singleSql = $query->sql;
            if ($query->bindings) {
                foreach ($query->bindings as $replace) {
                    $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                    $singleSql = preg_replace('/\?/', $value, $singleSql, 1);
                }
            }

            static::push($singleSql);

            if (static::isAllowDump()) {
                $prefix = '';
                if ($query->time >= 500) { // 慢查询
                    $prefix = "查询耗时（{$query->time}ms）:";
                }
                dump($prefix . $singleSql);
            }
        });
    }

    /**
     * 开始启动
     * @param mixed|null $enableDump
     * @return string
     */
    public static function booted(mixed $enableDump = null): string
    {
        if (!is_null($enableDump)) {
            self::enableDump((bool) $enableDump);
        }

        if (!self::$booted) {
            self::$booted = true;

            static::boot();
        }

        return self::class;
    }

    /**
     * 启动并打印SQL
     * @return string
     */
    public static function bootedWithDump(): string
    {
        return static::booted(true);
    }

    /**
     * 压入一条SQL语句
     * @param string $sql
     * @return string
     */
    protected static function push(string $sql): string
    {
        self::$sqlList[] = $sql;
        array_splice(self::$sqlList, 0, -self::$maxCacheSqlCount);
        self::$lastSql = end(self::$sqlList);

        return self::class;
    }

    /**
     * 获取最后一条SQL
     * @return string
     */
    public static function sql(): string
    {
        static::booted();

        return static::$lastSql;
    }

    /**
     * 获取最后一组SQL
     * @param int $length
     * @return array
     */
    public static function sqls(int $length = 0): array
    {
        return $length ? array_slice(self::$sqlList, -$length) : self::$sqlList;
    }

    /**
     * 打印SQL
     * @param int $length
     * @return string
     */
    public static function dump(int $length = 1): string
    {
        $sqls = self::sqls($length);
        static::isAllowDump() && dump(...$sqls);

        return self::class;
    }

    /**
     * 打印SQL
     * @param int $length
     */
    public static function dd(int $length = 1)
    {
        $sqls = self::sqls($length);
        static::isAllowDump() && dd(...$sqls);
    }

    /**
     * 打印SQL
     */
    public static function ddMax()
    {
        static::isAllowDump() && dd(...self::$sqlList);
    }

    /**
     * 开启实时打印（线上环境将不会打印）
     * @param bool $enableDump
     * @return string
     */
    public static function enableDump(bool $enableDump = true)
    {
        self::$enableDump = $enableDump;

        return self::class;
    }

    /**
     * 强制开启实时打印
     * @param bool $enableDump
     * @return string
     */
    public static function forceEnableDump(bool $enableDump = true)
    {
        static::$forceEnableDump = $enableDump;

        return self::class;
    }

    /**
     * 是否允许打印
     * @return bool
     */
    public static function isAllowDump()
    {
        return static::$forceEnableDump || (app()->environment() != 'production' && static::$enableDump);
    }
}
