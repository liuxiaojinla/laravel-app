<?php

namespace App\Repositories;

use App\Core\Traits\HasUseCompanyId;
use Illuminate\Database\Eloquent\Builder;

trait WithUseCompanyId
{
    use HasUseCompanyId;

    /**
     * @var bool
     */
    protected $enableUseCompanyId = true;

    /**
     * @var string
     */
    protected $companyIdField = 'company_id';

    /**
     * 是否 CompanyId 约束
     * @return bool
     */
    protected function isUseCompanyIdEnabled()
    {
        // 自动匹配机制
        if ($this->enableUseCompanyId === null) {
            // 控制台模式情况下不强制要求 CompanyId 
            $isEnable = !app()->runningInConsole();
        } else { // 其他情况
            $isEnable = $this->enableUseCompanyId;
        }

        return $isEnable;
    }

    /**
     * 数据源挂载 CompanyId
     * @param array $data
     * @return void
     */
    protected function attachCompanyId(&$data)
    {
        $isEnable = $this->isUseCompanyIdEnabled();
        if (!$isEnable) {
            return;
        }

        $data[$this->companyIdField] = $this->getUseCompanyId(true);
    }

    /**
     * 数据源去除 CompanyId 字段
     * @param array $data
     */
    protected function detachUseCompanyId($data)
    {
        $isEnable = $this->isUseCompanyIdEnabled();
        if (!$isEnable) {
            return;
        }

        unset($data[$this->companyIdField]);
    }

    /**
     * 应用 CompanyId 查询
     * @param Builder $builder
     * @return Builder
     */
    protected function applyUseCompanyId(Builder $builder)
    {
        $isEnable = $this->isUseCompanyIdEnabled();
        if (!$isEnable) {
            return $builder;
        }

        $companyId = $this->getUseCompanyId(true);
        $builder->where($this->companyIdField, $companyId);

        return $builder;
    }
}
