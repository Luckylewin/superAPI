<?php

namespace App\Service\ott;

class youtube extends ottbase
{
    public $key = array('name');
    public $resolveKey = array('name');

    public $expireTime = 1800;

    public function getKey()
    {
        return $this->key;
    }

    public function getResolveKey()
    {
        return $this->resolveKey;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * 取多种清晰度的播放地址
     * @param $data
     * @return array|bool|string
     */
    public function getPlayList($data)
    {
        $id = $data['name'];

        if (preg_match('/\w+/', $id) == false ) {
            return '403 forbidden';
        }

        $url = "https://www.youtube.com/watch?v={$id}";
        $url = escapeshellarg($url);

        $string = "youtube-dl -J {$url}";
        exec($string, $out, $status);
        if ($status != 0 || !isset($out[0])) {
            return false;
        }

        $data = $out[0];
        $data = json_decode($data, true);
        $videos = [];

        $formats = $data['formats'];
        foreach ($formats as $format) {

            if (strpos($format['format'], 'video only') === false &&
                isset($format['url']) &&
                $format['ext'] == 'mp4') {
                $videos[$format['format_note']] = $format['url'];
            }
        }

        return $videos;
    }

    public function getUrl($data)
    {
        $id = $data['name'];
        if (preg_match('/\w+/', $id) == false ) {
            return '403 forbidden';
        }

        $url = "https://www.youtube.com/watch?v={$id}";
        $url = escapeshellarg($url);

        $string = "youtube-dl -J {$url}";
        exec($string, $out, $status);
        if ($status != 0 || !isset($out[0])) {
            return false;
        }

        $data = $out[0];

        $data = json_decode($data, true);

        $videos = [];

        $formats = $data['formats'];
        foreach ($formats as $format) {

            if (strpos($format['format'], 'video only') === false &&
                isset($format['url']) &&
                $format['ext'] == 'mp4') {
                $videos[$format['format_note']] = $format['url'];
            }
        }

        return end($videos);

    }

}
