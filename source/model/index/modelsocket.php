<?php

namespace ng169\model\index;

use ng169\lib\Log;
use ng169\tool\Out;
use ng169\Y;

checktop();

class modelsocket extends Y
{
    public function isonlie($fd) {}
    public function loginadmin($fd, $data)
    {
        if (!$fd) return false;
        if (!$data) return false;
        if (!isset($data['uid'])) return false;
        $add = [];
        $add['type'] = 1;
        $add['uname'] = $data['uid'];
        $add['resource'] = $fd;
        $add['addtime'] = time();
        $add['online'] = 1;
        $flag = T('sock_client')->add($add);
        return $flag;
    }
    public function login($fd, $data)
    {
        if (!$fd) return false;
        if (!$data) return false;
        if (!isset($data['uid'])) return false;
        $add = [];
        $add['type'] = 0;
        $add['uname'] = $data['uid'];
        $add['online'] = 1;
        $add['resource'] = $fd;
        $add['addtime'] = time();
        $flag = T('sock_client')->add($add);
        return $flag;
    }
    public function loginout($fd)
    {
        if (!$fd) return false;
       
        $add = [];
        $add['online'] = 0;
        $w = ['resource' => $fd];
        $flag = T('sock_client')->update($add,$w);
        return $flag;
    }
}
