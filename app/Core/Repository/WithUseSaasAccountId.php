<?php

namespace App\Core\Repository;

use App\Core\Saas\HasUseSaasAccountId;
use Illuminate\Database\Eloquent\Builder;

trait WithUseSaasAccountId
{
    use HasUseSaasAccountId;

    /**
     * @var string
     */
    protected $saasAccountIdField = null;

    /**
     * 数据源挂载 SaasAccountI
     * @param array $data
     * @return void
     */
    protected function attachUseSaasAccountId(&$data)
    {
        $isEnable = $this->isUseSaasAccountIdEnabled();
        if (!$isEnable) {
            return;
        }

        $data[$this->saasAccountIdField] = $this->getUseSaasAccountId(true);
    }

    /**
     * 数据源去除 SaasAccountI 字段
     * @param array $data
     */
    protected function detachUseCompanyId($data)
    {
        $isEnable = $this->isUseSaasAccountIdEnabled();
        if (!$isEnable) {
            return;
        }

        unset($data[$this->saasAccountIdField]);
    }

    /**
     * 应用 SaasAccountI 查询
     * @param Builder $builder
     * @return Builder
     */
    protected function applyUseCompanyId(Builder $builder)
    {
        $isEnable = $this->isUseSaasAccountIdEnabled();
        if (!$isEnable) {
            return $builder;
        }

        $saasAccountId = $this->getUseSaasAccountId(true);
        $builder->where($this->saasAccountIdField, $saasAccountId);

        return $builder;
    }
}
