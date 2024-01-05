<?php

namespace App\Providers;

use App\Exceptions\Error;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Xin\Support\Str;
use Xin\Support\Time;

class RequestServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        /**
         * 获取 ID list
         *
         * @param string $field
         * @return array
         */
        Request::macro('ids', function ($field = 'ids') {
            /** @var $this Request */
            $ids = $this->input($field);

            return $ids ? Str::explode($ids) : [];
        });

        /**
         * 获取分页参数
         *
         * @param bool $withQuery
         * @return array
         */
        Request::macro('paginate', function ($withQuery = true) {
            /** @var $this Request */
            $param = [
                'page' => $this->page(),
            ];

            if ($this->has('limit')) {
                $param['list_rows'] = $this->limit();
            }

            if ($withQuery) {
                $param['query'] = $this->query();
            }

            return $param;
        });

        /**
         * 获取页码
         *
         * @return int
         */
        Request::macro('page', function () {
            /** @var $this Request */
            $page = (int)$this->input('page', 0);
            $page = max($page, 1);

            return (int)$page;
        });

        /**
         * 获取分页条数
         *
         * @param int $max
         * @param int $default
         * @return int
         */
        Request::macro('page', function (int $max = 100, int $default = 15) {
            /** @var $this Request */
            $limit = (int)$this->input('limit', 0);
            if ($limit < 1) {
                $limit = $default;
            } else {
                $limit = min($limit, $max);
            }

            return (int)$limit;
        });

        /**
         * 获取记录偏移数
         *
         * @return int
         */
        Request::macro('offset', function () {
            /** @var $this Request */
            $offset = (int)$this->input('offset', 0);
            $offset = max($offset, 1);

            return (int)$offset;
        });

        /**
         * 获取排序的字段
         *
         * @return string
         */
        Request::macro('sort', function () {
            /** @var $this Request */
            // todo 重新优化
            return $this->input('sort', '');
        });

        /**
         * 获取范围时间
         *
         * @param string $field
         * @param int $maxRange
         * @param string $delimiter
         * @return array
         */
        Request::macro('rangeTime', function (string $field = 'datetime', int $maxRange = 0, string $delimiter = ' - ') {
            /** @var $this Request */
            $rangeTime = $this->input($field, '');

            return Time::parseRange($rangeTime, $maxRange, $delimiter);
        });

        /**
         * 获取筛选关键字
         *
         * @param string $field
         * @return string
         */
        Request::macro('keywords', function (string $field = 'keywords') {
            /** @var $this Request */
            if ($this->has($field, 'get')) {
                $keywords = (string)$this->query($field, '');
            } else {
                $keywords = (string)$this->post($field, '');
            }
            $keywords = trim($keywords);

            return Str::rejectEmoji($keywords);
        });

        /**
         *  获取关键字SQL
         *
         * @param string $field
         * @return array
         */
        Request::macro('keywordsSql', function (string $field = 'keywords') {
            /** @var $this Request */
            return keywords_build_sql($this->keywords($field));
        });


        /**
         * 获取ID并验证
         *
         * @param string $field
         * @return int
         * @see validId alias
         */
        Request::macro('validId', function ($field = 'id') {
            /** @var $this Request */
            $id = (int)$this->input("{$field}");
            if ($id < 1) {
                throw Error::validate("param {$field} invalid.");
            }

            return $id;
        });

        /**
         * 获取ID 列表
         *
         * @param string $field
         * @return array
         */
        Request::macro('validIds', function ($field = 'ids') {
            /** @var $this Request */
            $ids = $this->ids($field);
            if (empty($ids)) {
                throw  Error::validate("param {$field} invalid.");
            }

            return $ids;
        });

        /**
         * 获取整形数据并验证
         *
         * @param string $field
         * @param array $values
         * @param mixed $default
         * @return int
         */
        Request::macro('validIntIn', function ($field, $values, $default = null) {
            /** @var $this Request */
            $value = $this->input($field);
            if ($value === '' || $value === null) {
                $value = $default;
            }
            $value = (int)$value;
            if (!in_array($value, $values, true)) {
                throw Error::validate("param {$field} invalid.");
            }

            return $value;
        });

        /**
         * 获取字符串数据并验证
         *
         * @param string $field
         * @param mixed $default
         * @param string $filter
         * @return string
         */
        Request::macro('validString', function ($field, $default = null, $filter = '') {
            /** @var $this Request */
            $value = $this->input($field, $default);
            if ($filter) {
                $value = call_user_func($filter, $value);
            }

            if (empty($value)) {
                throw Error::validate("param {$field} invalid.");
            }

            return $value;
        });
    }
}
