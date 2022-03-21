<?php

namespace ng169\control\index;

use ng169\control\indexbase;

checktop();

class pay extends indexbase
{

    protected $noNeedLogin = [''];

    public function control_run()
    {
        $res = M('order', 'im')->get_charge($this->get_userid());
        $data = T('third_party_user')->set_field('golden_bean,remainder')->set_where(['id' => $this->get_userid()])->get_one();

        $this->view(null, ['data' => $res, 'wallet' => $data]);
    }
}
