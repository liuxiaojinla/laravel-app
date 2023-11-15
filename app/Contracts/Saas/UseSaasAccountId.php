<?php

namespace App\Contracts\Saas;

/**
 * 使用 SaasAccountId
 */
interface UseSaasAccountId
{

    /**
     * 设置 SaasAccountId
     * @param int $saasAccountId
     * @return void
     */
    public function setUseSaasAccountId(int $saasAccountId);

    /**
     * 获取 SaasAccountId
     * @param bool $failException
     * @return int
     */
    public function getUseSaasAccountId($failException = true);

    /**
     * 是否 SaasAccountId 约束
     * @return bool
     */
    public function isUseSaasAccountIdEnabled();
}
