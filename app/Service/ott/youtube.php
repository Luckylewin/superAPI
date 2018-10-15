<?php

namespace App\Service\ott;

class youtube extends ottbase
{
    public $key = array('name');
    public $expireTime = 2;

    public function getKey()
    {
        return $this->key;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function getUrl($data)
    {
        $id = $data['id'];
        if (preg_match('/\w+/', $id) == false ) {
            return '403 forbidden';
        }

        $url = "https://www.youtube.com/watch?v={$id}";
        $url = escapeshellarg($url);
        
        $string = "youtube-dl -g {$url}";
        exec($string, $out, $status);

        return $out;
    }


}
