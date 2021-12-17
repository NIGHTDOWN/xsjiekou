<?php

namespace ng169\control\index;

use ng169\control\indexbase;

checktop();

class category  extends indexbase
{

    protected $noNeedLogin = ['*'];

    public function control_run()
    {

        $data = M('cate', 'im')->getcate($this->langid);

        $catejson = json_encode($data);

        $lable0 = $data[0]['child'][0]['tag'];

        $this->view(null, ['data' => $data, 'catejson' => $catejson, 'lb0' => $lable0]);
    }
    public function control_page()
    {

        $data = M('cate', 'im')->getcate($this->langid);

        $catejson = json_encode($data);

        $lable0 = $data[0]['child'][0]['tag'];

        $this->view(null, ['data' => $data, 'catejson' => $catejson, 'lb0' => $lable0]);
    }
}
