<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/21
 * Time: 11:14
 */

namespace App\Models;


class KaraokeSearcher extends Searcher
{
    public $name;
    public $lang;
    public $page;
    public $perPage;
    public $sort;
    public $tags;
}