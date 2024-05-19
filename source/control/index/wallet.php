<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class wallet extends indexbase
{

    protected $noNeedLogin = [''];

    public function control_run()
    {
        $data = T('third_party_user')->set_field('golden_bean,remainder')->set_where(['id' => $this->get_userid()])->get_one();
        Out::jout($data);
    }
    public function control_unlock()
    {
        $get = get(['int' => ['bookid' => 1, 'type' => 1, 'sid' => 1, 'autopay']]);
        // $uid, $bookid, $type, $sid, $autopay
        $ret = M('user', 'im')->unlock($this->get_userid(), $get['bookid'], $get['type'], $get['sid'], $get['autopay']);
      
        Out::jout($ret);
    }
    public function control_bean()
    {
        $pages = get(['int' => ['page', 'ajax']]);
        $list = M('coin', 'im')->charge($this->get_userid(), $pages['page']);
        if ($pages['ajax']) {
            Out::jout($list);
        } else {
            $this->view(null, ['data' => $list]);
        }
    }
    public function control_star()
    {
        $pages = get(['int' => ['page', 'ajax']]);
        $list = M('coin', 'im')->record($this->get_userid(), $pages['page']);
        if ($pages['ajax']) {
            Out::jout($list);
        } else {
            $this->view(null, ['data' => $list]);
        }
    }
}
