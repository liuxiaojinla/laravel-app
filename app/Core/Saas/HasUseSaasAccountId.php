<?php

namespace App\Core\Saas;

/**
 * @mixin \App\Contracts\Saas\UseSaasAccountId
 */
trait HasUseSaasAccountId
{
    /**
     * @var int
     */
    protected $useSaasAccountId = 0;

    /**
     * @var bool
     */
    protected $enableUseSaasAccountId = true;

    /**
     * 设置 SaasAccountId
     * @param int $saasAccountId
     * @return void
     */
    public function setUseSaasAccountId(int $saasAccountId)
    {
        $this->useSaasAccountId = $saasAccountId;
    }

    /**
     * 获取 CompanyId
     * @param bool $failException
     * @return int
     */
    public function getUseSaasAccountId($failException = true)
    {
        if (empty($this->useSaasAccountId) && $failException) {
            throw new \LogicException(static::class . '->setUseSaasAccountId must be set.');
        }

        return $this->useSaasAccountId;
    }

    /**
     * 是否 SaasAccountId 约束
     * @return bool
     */
    public function isUseSaasAccountIdEnabled()
    {
        // 自动匹配机制
        if ($this->enableUseSaasAccountId === null) {
            // 控制台模式情况下不强制要求 CompanyId
            $isEnable = !app()->runningInConsole();
        } else { // 其他情况
            $isEnable = $this->enableUseSaasAccountId;
        }

        return $isEnable;
    }
}
