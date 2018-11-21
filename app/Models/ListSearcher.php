<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/21
 * Time: 10:42
 */

namespace App\Models;


class ListSearcher extends Searcher
{
    public $cid;
    public $genre;
    public $field;
    public $items_perpage;
    public $items_page;
}