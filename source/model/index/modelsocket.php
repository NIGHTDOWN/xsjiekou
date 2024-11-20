<?php

namespace ng169\model\index;

use ng169\lib\Log;
use ng169\model\Xmodel;
use ng169\tool\Out;
use ng169\Y;
checktop();

class modelsocket extends Xmodel
{
    public function isonlie($fd) {}
    public function loginadmin(&$db,$fd, $data)
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
        //判断resource是否存在；不存在就添加；存在就修改
        $w = ['resource' => $fd];
        $model = new Xmodel('sock_client');
        $model=$model->injectdb($db);
        $info = $model->get_one($w);
        if ($info) {
            $flag = $model->update($add, $w);
        } else {
            $flag = $model->add($add);
        }
        return $flag;
    }
    public function login(&$db,$fd, $data, $http = null)
    {
        if (!$fd) return false;
        if (!$data) return false;
        if (!isset($data['uid'])) return false;

        //从fd获取客户端ip以及端口
        $ip = $http->getClientInfo($fd)['remote_ip'];
        $port = $http->getClientInfo($fd)['remote_port'];
        // $ip = $this->http->getClientInfo($fd)['remote_ip'];
        // $port = $this->http->getClientInfo($fd)['remote_port'];

        if ($data['uid'] == 0) {
            $uuid = $data['uuid'];
            $uid = $this->inanmous($db,$uuid);
            $type = 2;
        } else {
            $uid = $data['uid'];
            $type = 1;
        }
        $add = [];
        $add['type'] = $type;
        $add['uname'] = $uid;
        $add['online'] = 1;
        $add['ip'] = $ip;
        $add['port'] = $port;
        $add['resource'] = $fd;
        $add['addtime'] = time();
        //判断resource是否存在；不存在就添加；存在就修改
        $w = ['resource' => $fd];
        $model = new Xmodel('sock_client');
        $model=$model->injectdb($db);
        $info = $model->get_one($w);
        if ($info) {
            $flag = $model->update($add, $w);
        } else {
            $flag = $model->add($add);
        }

        return $add;
    }
    //匿名用户插入生成的uid
    public function inanmous(&$db,$uuid)
    {
        //判断是否存在
        $w = ['uuid' => $uuid];
        $model = new Xmodel('anmous');
        $model=$model->injectdb($db);
        $info = $model->set_field("uid")->get_one($w);
        if ($info) {
            return $info['uid'];
        }
        $add = [];
        $add['uuid'] = $uuid;
        $add['addtime'] = time();
        $id = $model->add($add);
        return $id;
    }
    public function getuid(&$db,$fd)
    {
        $w = ['resource' => $fd];
        $model = new Xmodel('sock_client');
        $model=$model->injectdb($db);
        $info =  $model->get_all($w);
        return $info['uname'];
    }
    public function getadminfds(&$db)
    {
        $w = ['type' => 0, "online" => 1];
        $model = new Xmodel('sock_client');
        $model=$model->injectdb($db);
        $info =  $model->get_all($w);
        $adminids = array_column($info, 'resource');
        return $adminids;
    }
    public function getclientfds(&$db,$uid)
    {
        $w = ['type' => [1, 2], "online" => 1, "uname" => $uid];
        $model = new Xmodel('sock_client');
        $model=$model->injectdb($db);
        $info =  $model->get_all($w);
        $ids = array_column($info, 'resource');
        return $ids;
    }
    public function loginout(&$db,$fd)
    {
        if (!$fd) return false;
        $add = [];
        $add['online'] = 0;
        $w = ['resource' => $fd,];
        $model = new Xmodel('sock_client');
        $model=$model->injectdb($db);
        $user= $model->get_one($w);
        if($user){
            $flag = $model->update($add, $w);
        }
        return $user;
    }
}
