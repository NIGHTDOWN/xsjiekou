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
        $add['online'] = 1;
        $add['resource'] = $fd;
        $add['addtime'] = time();
        //判断resource是否存在；不存在就添加；存在就修改
        $w = ['resource' => $fd];
        $info = T('sock_client')->get_one($w);
        if ($info) {
            $flag = T('sock_client')->update($add, $w);
        } else {
            $flag = T('sock_client')->add($add);
        }
        return $flag;
    }
    public function login($fd, $data)
    {
        if (!$fd) return false;
        if (!$data) return false;
        if (!isset($data['uid'])) return false;
        $clientInfo = socket_getpeername($fd, $ip, $port);
        if ($clientInfo === false) {
            // 处理获取信息失败的情况
          d($ip);
          d($port);
        }

        // 现在$ip和$port变量包含了客户端的IP地址和端口
        // 你可以在这里使用它们
        // 例如，打印出来
        echo "客户端IP地址: " . $ip . ", 端口: " . $port;
        //从fd获取客户端ip以及端口
        // $ip = $this->http->getClientInfo($fd)['remote_ip'];
        // $port = $this->http->getClientInfo($fd)['remote_port'];
        d($fd);
        d($data);
        $add = [];
        $add['type'] = 0;
        $add['uname'] = $data['uid'];
        $add['online'] = 1;
        $add['resource'] = $fd;
        $add['addtime'] = time();
        //判断resource是否存在；不存在就添加；存在就修改
        $w = ['resource' => $fd];
        $info = T('sock_client')->get_one($w);
        if ($info) {
            $flag = T('sock_client')->update($add, $w);
        } else {
            $flag = T('sock_client')->add($add);
        }

        return $flag;
    }
    public function getuid($fd)
    {
        $w = ['resource' => $fd];
        $info = T('sock_client')->get_one($w);
        return $info['uname'];
    }
    public function getadminfds()
    {
        $w = ['type' => 1, "online" => 1];
        $info = T('sock_client')->get_all($w);
        $adminids = array_column($info, 'resource');
        return $adminids;
    }
    public function getclientfds($uid)
    {
        $w = ['type' => 0, "online" => 1, "uname" => $uid];
        $info = T('sock_client')->get_all($w);
        $ids = array_column($info, 'resource');
        return $ids;
    }
    public function loginout($fd)
    {
        if (!$fd) return false;

        $add = [];
        $add['online'] = 0;
        $w = ['resource' => $fd];
        $flag = T('sock_client')->update($add, $w);
        return $flag;
    }
}
