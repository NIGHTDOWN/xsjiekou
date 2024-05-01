<?php

namespace ng169\control\api;

use ng169\control\apibase;

checktop();
class log extends apibase
{
    protected $noNeedLogin = [];
    public function control_charge()
    {

        $pages = get(['int' => ['page']]);
        $list = M('coin', 'im')->charge($this->get_userid(), $pages['page']);
        $this->returnSuccess($list);
    }
    public function control_expend()
    {
        $pages = get(['int' => ['page']]);
        $list = M('coin', 'im')->expand_his($this->get_userid(), $pages['page']);
        $this->returnSuccess($list);
    }
    public function control_record()
    {
        $pages = get(['int' => ['page']]);
        $list = M('coin', 'im')->record($this->get_userid(), $pages['page']);

        $this->returnSuccess($list);
    }
}
