<?php

namespace ng169\control\index;

use ng169\control\indexbase;
use ng169\tool\Out;

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
        $data = get(['int' => ['c1', 'c2', 'c3', 'c4', 'c5', 'page']]);
        $data = M('cate', 'im')->getlist($this->langid, $data['c1'], $data['c2'], $data['c3'], $data['c4'], $data['c5'], $data['page']);
        
        if ($_POST) {
            Out::jout($data);
        } else {
            $this->view(null, ['data' => $data]);
        }
    }
}
