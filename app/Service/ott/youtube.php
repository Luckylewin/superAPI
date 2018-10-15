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
        $url = escapeshellarg($url);

        $string = "sudo youtube-dl -g {$url}";

        $descriptorSpec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w"),  // stderr
        );
        $process = proc_open($string, $descriptorSpec, $pipes);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);

        return $stdout;
    }


}
