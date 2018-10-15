<?php

namespace App\Service\ott;

class youtube extends ottbase
{
    public $key = array('name');
    public $expireTime = 3600;

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
        $data = exec("youtube-dl -g {$url}", $out, $status);
        
        return $out;
    }


}
