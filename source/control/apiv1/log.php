<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;

checktop();
class log extends apiv1base
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
