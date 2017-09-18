<?php
namespace app\common\tools;

/**
 * 获取受访者信息
 */
class Strings
{

    /**
     * 替换字符串中间位置字符为星号
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-17T10:29:46+0800
     * @param  [type] $str [description]
     * @return [type] [description]
     */
    public static function replaceToStar($str)
    {
        $len = strlen($str) / 2;

        return substr_replace($str, str_repeat('*', $len), floor(($len) / 2), $len);
    }

    /**
     * 获取文件访问地址
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-24T12:46:17+0800
     * @param string $value [description]
     */
    public static function fileWebLink($realPath)
    {
        $replace = dirname(ROOT_PATH);

        return str_replace($replace, '', $realPath);
    }

    /**
     * 通过文件访问地址获取 文件绝对地址
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-31T10:10:03+0800
     * @param  string $value [description]
     * @return [type] [description]
     */
    public static function fileWebToServer($webLink)
    {
        $replace = dirname(ROOT_PATH);

        return $replace . $webLink;
    }

    /**
     * 删除文件
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-06-01T15:31:35+0800
     * @param  string $value [description]
     * @return [type] [description]
     */
    public static function deleteFile($filename)
    {
        if (!file_exists($filename)) {
            $filename = self::fileWebToServer($filename);
        }

        if (file_exists($filename) && is_file($filename)) {
            unlink($filename);
        }
    }
    /**
     * [password description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-05-31T10:09:45+0800
     * @param  [type] $string [description]
     * @return [type] [description]
     */
    public static function password($string)
    {
        return md5($string);
    }
}
