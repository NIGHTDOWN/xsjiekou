<?php

namespace ng169\model\index;

use ng169\Y;
use ng169\tool\Out;

checktop();

class version
{
    public $stop_verion = 'stop_verion';
    public $dsldomain = 'dsl_domain';
    public $check_version = 'check_version';
    public function getcache($name)
    {
        list($bool, $data) = Y::$cache->get($name);
        if ($bool) {
            return $data;
        } else {
            $datatmp = T('option')->set_where(['option_name' => $name])->get_one();
            if (!$datatmp) {
                return false;
            }
            $version = $datatmp['option_value'];
            Y::$cache->set($name, $version, G_DAY);
            return $version;
        }
    }
    public function getdsl(){
        $dbversion = $this->getcache($this->dsldomain);
        return $dbversion;
    }
    //锁定旧版本
    public function lockold($version)
    {
        $lockstring = __('版本太旧了，请更新到最新版本。下载地址') . "\n https://www.love-novel.com";
       
        $dbversion = $this->getcache($this->stop_verion);
        if (!$dbversion) {
            //后台未配置版本限制。直接返回
            return false;
        }
        if (!$version) {
            Out::jerror($lockstring, null, '100130');
        }
        if (version_compare($version, $dbversion, '<')) {
            Out::jerror($lockstring, null, '100130');
        }
    }
    //谷歌上架时候无书籍数据
    public function cheknew($version)
    {
        $dbversion = $this->getcache($this->check_version);
        if (!$dbversion) {
            return false;
        }
        if (version_compare($version, $dbversion, '=')) {
            Out::jout([]);
        }
    }
}
