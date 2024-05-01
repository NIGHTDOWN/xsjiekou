<?php

namespace ng169\cache;

checktop();
class File
{
    #缓存目录
    private $dirName;
    #超时时长
    private  $timeout;
    #编码设置
    private $charset;
    public
    function __construct($timeout = null, $charset = 'UTF-8', $cachePath = '/data/cache/')
    {
        $this->timeout = $timeout ? $timeout : CACHE_TIMEOUT;
        $this->charset = $charset;
        $this->dirName = ROOT . $cachePath;
        if (!is_dir($this->dirName)) {
            error($this->dirName . '目录不存在或者不可以读');
        }
        return $this;
    }


    private
    function _unSerialize($string)
    {
        if (!empty($string)) {
            if (strtolower($this->charset) == 'utf-8') {
                return $this->_utf_unserialize($string);
            } else {
                return $this->_gbk_unserialize($string);
            }
        } else {
            return '';
        }
    }
    private
    function _utf_unserialize($serial_str)
    {
        $serial_str = preg_replace(
            '!s:(\d+):"(.*?)";!s',
            "'s:'.strlen('$2').':\"$2\";'",
            $serial_str
        );
        $serial_str = str_replace("\r", "", $serial_str);
        return @unserialize($serial_str);
    }
    private
    function _gbk_unserialize($serial_str)
    {
        $serial_str = preg_replace(
            '!s:(\d+):"(.*?)";!se',
            '"s:".strlen("$2").":\"$2\";"',
            $serial_str
        );
        $serial_str = str_replace("\r", "", $serial_str);
        return @unserialize($serial_str);
    }

    public
    function set($name, $value, $timeout = null)
    {

        if (!$name) return false;
        $cachefile = $this->dirName . "cache_$name.cache";

        @$fp        = fopen($cachefile, 'wb');
        //写入失败，不要中断
        if (!$fp) return false;
        //  or error(
        //     '读取缓存数据失败。如果您使用的是Unix/Linux主机，请修改缓存目录 (' . $this->dirName . ') 下所有文件的权限为777。如果您使用的是Windows主机，请联系管理员，将该目录下所有文件设为everyone可写',
        //     '',
        //     1
        // );

        $data = array('time'   => $_SERVER['REQUEST_TIME'], 'timeout' => isset($timeout) ? $timeout : $this->timeout, 'data'   => $value);

        $cacheData = serialize($data);
        @$fw        = fwrite($fp, $cacheData);
        //  or error('写入缓存数据失败，缓存目录 (' . $this->dirName . ') 不可写');
        @fclose($fp);
        return true;
    }
    public
    function get($name)
    {

        if (!$name) return false;
        $cache_data = null;
        $cachefile = $this->dirName . "cache_$name.cache";
        if (!is_file($cachefile) || filesize($cachefile) <= 0) {
        } else {
            if ($fp = fopen($cachefile, 'r')) {
                $data       = fread($fp, filesize($cachefile));
                fclose($fp);

                //                $cache_data = $this->_unSerialize($data);
                $cache_data = unserialize($data);

                return  $data = $this->_cehck($cache_data);
            }
        }
        return array(false, $cache_data);
    }
    public
    function del($name = null)
    {
        if (!$name) return false;
        //直接赋值false;
        // $this->set($name, false, 1);
        $cachefile = $this->dirName . "cache_$name.cache";

        if (!file_exists($cachefile)) {
            //缓存不存在
            return false;
        }
        if (is_file($cachefile)) {
            return unlink($cachefile);
        }
    }
    public
    function clear($path)
    {
        $path = $path ? $path : DATA . '/cache/';
        if (is_dir($path)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($path . $val)) {
                        //子目录中操作删除文件夹和文件
                        $this->clear($path . $val . '/');
                        //目录清空后删除空文件夹
                        // @rmdir($path . $val . '/');
                    } else {
                        //如果是文件直接删除
                        unlink($path . $val);
                    }
                }
            }
        }
    }
    private
    function _cehck($value)
    {
        if (is_array($value)) {
            if ($value['timeout'] != 0 && ($value['timeout'] + $value['time']) > $_SERVER['REQUEST_TIME']) {
                return array(true, $value['data']);
            } else
            if ($value['timeout'] == 0) {
                return array(true, $value['data']);
            } else {
                return array(false, false);
            }
        } else {
            return array(false, false);
        }
    }
}
