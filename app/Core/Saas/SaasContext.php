<?php

namespace App\Core\Saas;

use Illuminate\Support\Facades\Cache;

class SaasContext
{
    /**
     * @var bool
     */
    protected static bool $isStrict = true;

    /**
     * @var ?string
     */
    protected ?string $identifier = null;

    /**
     * @var mixed|null
     */
    protected mixed $info = null;

    /**
     * @var mixed|null
     */
    protected mixed $agent = null;

    /**
     * @var string|null
     */
    protected static ?string $useIdentifier = null;

    /**
     * @var array<SaasContext>
     */
    protected static array $instances = [];

    /**
     * @param string|null $identifier
     */
    protected function __construct(string $identifier = null)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * 获取Sass账号信息
     * @return mixed
     * @throws SaasAccountException
     */
    public function getInfo()
    {
        if (!$this->info) {
            $this->info = $this->loadInfo();
        }

        return $this->info;
    }

    /**
     * 获取授权应用信息
     * @return mixed
     * @throws SaasAccountException
     */
    public function getAgent()
    {
        if (!$this->agent) {
            $this->agent = $this->loadAgent();
        }

        return $this->agent;
    }

    /**
     * 加载Saas账号信息
     * @return mixed
     * @throws SaasAccountException
     */
    protected function loadInfo()
    {
        $cacheKey = static::resolveCorpCacheKey($this->getIdentifier());
        $info = Cache::get($cacheKey);

        if (empty($info) || !$info instanceof WechatWorkCorp) {
            $info = WechatWorkCorp::query()->where('corp_id', $this->getCorpId())->latest()->first();
            if (empty($info)) {
                throw new SaasAccountException();
            }

            Cache::put($cacheKey, $info, now()->addMinutes(30));
        }

        return with($info);
    }

    /**
     * 加载应用信息
     * @return \App\Models\Wechat\WechatWorkAgent
     * @throws \App\Exceptions\CorpNotFoundException
     */
    protected function loadAgent()
    {
        $cacheKey = static::resolveAgentCacheKey($this->getIdentifier());
        $info = Cache::get($cacheKey);

        if (empty($info) || !$info instanceof WechatWorkAgent) {
            $info = WechatWorkAgent::query()->where('corp_id', $this->getCorpId())->latest()->first();
            if (empty($info)) {
                throw new SaasAccountException();
            }

            Cache::put($cacheKey, $info, now()->addMinutes(30));
        }

        return with($info);
    }

    /**
     * 获取一个实例
     * @return static
     */
    public static function instance(string $identifier = null, bool $refresh = false)
    {
        $identifier = $identifier ?: static::$useIdentifier;

        if (empty(static::$instances[$identifier]) || $refresh) {
            static::$instances[$identifier] = new static($identifier);
        }

        return static::$instances[$identifier];
    }

    /**
     * 使用
     * @param string $corpId
     * @param bool $refresh
     * @return static
     */
    public static function use(string $identifier, bool $refresh = false)
    {
        static::$useIdentifier = $identifier;

        return static::instance($identifier, $refresh);
    }

    /**
     * 尝试获得CorpId
     * @return string|null
     */
    public static function attemptAcquireIdentifier()
    {
        return static::isStrict() ? static::instance()->getIdentifier()
            : (static::$useIdentifier ?: null);
    }

    /**
     * 是否是严格模式
     * @return bool
     */
    public static function isStrict()
    {
        return static::$isStrict;
    }

    /**
     * @param bool $strict
     * @return void
     */
    public static function strict(bool $strict)
    {
        static::$isStrict = $strict;
    }

    /**
     * 刷新企业信息
     * @param string $corpId
     * @return void
     */
    public static function refresh($identifier)
    {
        static::destroy($identifier);
        static::use($identifier, true);
    }

    /**
     * 企业缓存信息
     * @param string $corpId
     * @return void
     */
    public static function destroy($identifier)
    {
        unset(static::$instances[$identifier]);

        Cache::forget(static::resolveCorpCacheKey($identifier));
        Cache::forget(static::resolveAgentCacheKey($identifier));
    }

    /**
     * 解析Corp缓存 key
     * @param string $corpId
     * @return string
     */
    protected static function resolveCorpCacheKey($identifier)
    {
        return "saas_account:info:{$identifier}";
    }

    /**
     * 解析Agent缓存 key
     * @param string $corpId
     * @return string
     */
    protected static function resolveAgentCacheKey($identifier)
    {
        return "saas_agent:agent:{$identifier}";
    }
}
