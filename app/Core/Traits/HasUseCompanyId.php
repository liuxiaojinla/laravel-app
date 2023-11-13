<?php

namespace App\Core\Traits;

/**
 * @mixin \App\Contracts\Base\UseCompanyId
 */
trait HasUseCompanyId
{
    /**
     * @var int
     */
    protected $useCompanyId = 0;

    /**
     * 设置 CompanyId
     * @param int $companyId
     * @return void
     * @deprecated
     */
    public function setCompanyId($companyId)
    {
        $this->setUseCompanyId($companyId);
    }

    /**
     * 获取 CompanyId
     * @return int
     * @deprecated
     */
    public function getCompanyId()
    {
        return $this->getUseCompanyId(false);
    }

    /**
     * 设置 CompanyId
     * @param int $companyId
     * @return void
     */
    public function setUseCompanyId(int $companyId)
    {
        $this->useCompanyId = $companyId;
    }

    /**
     * 获取 CompanyId
     * @param bool $failException
     * @return int
     */
    public function getUseCompanyId($failException = true)
    {
        if (empty($this->useCompanyId) && $failException) {
            throw new \LogicException(static::class . '->setUseCompanyId must be set.');
        }

        return $this->useCompanyId;
    }
}
