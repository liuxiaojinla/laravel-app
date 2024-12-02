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
        Request::macro('ids', function ($field = 'ids', $format = 'intval') {
            /** @var $this Request */
            $ids = $this->input($field);

            return $ids ? Str::explode($ids, $format) : [];
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
        Request::macro('limit', function (int $max = 100, int $default = 15) {
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
                throw Error::validationException("param {$field} invalid.");
            }

            return $id;
        });

        /**
         * 获取ID 列表
         *
         * @param string $field
         * @return array
         */
        Request::macro('validIds', function ($field = 'ids', $format = 'intval') {
            /** @var $this Request */
            $ids = $this->ids($field, $format);
            if (empty($ids)) {
                throw Error::validationException("param {$field} invalid.");
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
                throw Error::validationException("param {$field} invalid.");
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
                throw Error::validationException("param {$field} invalid.");
            }

            return $value;
        });

        /**
         * 获取当前请求的时间
         * @access public
         * @param bool $float 是否使用浮点类型
         * @return integer|float
         */
        Request::macro('time', function (bool $float = false) {
            /** @var $this Request */
            return $float ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');
        });

        /**
         * 获取当前请求的时间-格式化后的日期时间
         * @access public
         * @return string
         */
        Request::macro('timeFormat', function () {
            /** @var $this Request */
            return date('Y-m-d H:i:s', $this->time());
        });

        /**
         * 是否是GET请求
         * @access public
         * @return string
         */
        Request::macro('isGet', function () {
            /** @var $this Request */
            return strtoupper($this->method()) === 'GET';
        });

        /**
         * 是否是POST请求
         * @access public
         * @return string
         */
        Request::macro('isPost', function () {
            /** @var $this Request */
            return strtoupper($this->method()) === 'POST';
        });

        /**
         * 是否是PUT请求
         * @access public
         * @return string
         */
        Request::macro('isPut', function () {
            /** @var $this Request */
            return strtoupper($this->method()) === 'PUT';
        });

        /**
         * 是否是DELETE请求
         * @access public
         * @return string
         */
        Request::macro('isDelete', function () {
            /** @var $this Request */
            return strtoupper($this->method()) === 'DELETE';
        });

        /**
         * 是否是OPTIONS请求
         * @access public
         * @return string
         */
        Request::macro('isOptions', function () {
            /** @var $this Request */
            return strtoupper($this->method()) === 'OPTIONS';
        });
    }
}
