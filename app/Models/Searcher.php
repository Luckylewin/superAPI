<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/21
 * Time: 9:20
 */

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder;

class Searcher
{
    public $offset;
    public $limit;
    public $total;
    public $page_count;

    /**
     * @var Builder
     */
    public $query;

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function filterWhere($input, $condition)
    {
        list($column, $operator, $value) = $condition;

        if ($this->query && $input) {
            $this->query->where($column, $operator, $value);
        }

        return $this;
    }


    public function getQuery()
    {
        return $this->query;
    }

    public function getLinkParams($fields)
    {
        $params = [];
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                 $params[$field] = $this->$field;
            }
        }

        return $params;
    }

    public function getOffset($pageName = 'page', $perPageName = 'per_page')
    {
        return $this->offset = ($this->$pageName - 1) * $this->$perPageName;
    }

    public function getTotal()
    {
        if (!$this->total) {
            $this->total = $this->query->count();
        }

        return $this->total;
    }

    public function getPageCount($total, $perPageName = 'per_page')
    {
        if (empty($this->page_count)) {
            $this->page_count = ceil($total / $this->$perPageName );
        }

        return $this->page_count;
    }

    public function getDataByPage($pageName = 'page', $perPageName = 'per_page')
    {
        $offset = $this->getOffset($pageName, $perPageName);
        return $this->getQuery()->offset($offset)->limit($this->$perPageName)->get();
    }

    public function getRestMeta($pageName = 'page', $perPageName = 'per_page')
    {
        $total     = $this->getTotal();
        $pageCount = $this->getPageCount($total, $perPageName);

        return [
            'totalCount'   => $total,
            'pageCount'    => $pageCount,
            'currentPage'  => $this->$pageName,
            'perPage'      => $this->$perPageName
        ];
    }

    public function listen()
    {
        Capsule::connection()->enableQueryLog();  // 开启QueryLog
    }

    public function getRawSql()
    {
        print_r(Capsule::getQueryLog());
    }


}