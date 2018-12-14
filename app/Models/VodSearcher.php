<?php
/**
 * Created by PhpStorm.
 * User: lychee
 * Date: 2018/11/21
 * Time: 9:11
 */

namespace App\Models;


class VodSearcher extends Searcher
{
    public $cid;
    public $name;
    public $type;
    public $year;
    public $area;
    public $genre;
    public $per_page;
    public $page;
    public $letter;
    public $keyword;
}