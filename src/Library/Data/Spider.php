<?php
/**
 * 下载图片
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/5/17
 * Time: 18:03
 */

namespace Fengzyz\Data;


class Spider
{
    /**
     * 下载图片
     * @param $url
     * @param string $path
     */
    public static function downloadImage ($url, $path = 'images/', $fileName = '')
    {
        $header = array("Connection: Keep-Alive", "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3", "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        $file = curl_exec($ch);
        curl_close($ch);
        return self::saveAsImage($url, $file, $path, $fileName);
    }

    /**
     * 保持图片
     * @param $url
     * @param $file
     * @param $path
     */
    private static function saveAsImage ($url, $file, $path, $fileName = '')
    {

        $filename = $fileName ? $fileName . '.' . pathinfo($url, PATHINFO_EXTENSION) : pathinfo($url, PATHINFO_BASENAME);
        $resource = fopen($path . $filename, 'a');
        fwrite($resource, $file);
        fclose($resource);
        return $path . $filename;
    }


    private static function getFileExt ()
    {

    }
}