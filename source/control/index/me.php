<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class me extends indexbase
{

    protected $noNeedLogin = ['run'];


    public function control_run()
    {

        $this->view();
    }
    public function control_buyhis()
    {
        $pages = get(['int' => ['page', 'ajax']]);
        $list = M('coin', 'im')->expand_his($this->get_userid(), $pages['page']);
        if ($pages['ajax']) {
            Out::jout($list);
        } else {
            $this->view(null, ['data' => $list]);
        }
    }
    public function control_edit()
    {
        if ($_POST) {
            $get = get(['string' => ['avater', 'sex', 'nickname', 'more', 'borth']]);
            $get = array_filter($get);
            $bool = T('third_party_user')->update($get, ['id' => $this->get_userid(1)]);
            if ($bool) {
                $user = T('third_party_user')->get_one(['id' => $this->get_userid(1)]);
                $user['uid'] = $user['id'];
                $user['token'] = M('user', 'im')->gettoken($this->get_userid());
                Out::jout($user);
            } else {
                Out::jerror('100200', '编辑失败');
            }
        } else {
            $this->view(null);
        }
    }
    public function control_uphead()
    {
        $conf = $this->config;
        $confs = T('option')->get_one(['option_name' => 'upload_setting']);

        $confs = json_decode($confs['option_value'], 1);

        $conf['filetype'] = $confs['file_types']['image']['extensions'];
        $conf['upfilepath'] = $confs['upload_url'];
        $conf['upfilesize'] = $confs['file_types']['image']['upload_max_filesize'];
        $conf['save_url'] = $confs['save_url'];
        $upobj = new \ng169\tool\Upfile($conf);
        $f = '';
        if ($_FILES) {
            $out = null;
            foreach ($_FILES as $key => $name) {
                $a = $upobj->upload($key);
                if (!$a['flag']) {
                    Out::jerror($a['error']);
                }
                if ($a['data']['source']) {
                    $f .= $a['data']['source'] . ',';
                }
            }
            $f = trim($f, ',');
            Out::jout($f);
        }
    }
}
