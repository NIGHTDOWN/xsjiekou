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
