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
        // $pages = get(['int' => ['page', 'ajax']]);
        // $list = M('coin', 'im')->expand_his($this->get_userid(), $pages['page']);
        // if ($pages['ajax']) {
        //     Out::jout($list);
        // } else {
        //     $this->view(null, ['data' => $list]);
        // }
        $this->view(null);
    }
}
