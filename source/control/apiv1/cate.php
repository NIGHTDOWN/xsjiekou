<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\Y;
use ng169\tool\Request;

checktop();
class cate extends apiv1base
{

    protected $noNeedLogin = ['*'];
    //删除书籍相关缓存
    public function control_get()
    {
        $data = get(['int' => ['c1', 'c2', 'c3', 'c4', 'sex', 'page']]);
        $data = M('cate', 'im')->getlist($this->head['cityid'], $data['c1'], $data['sex'], $data['c2'], $data['c3'], $data['c4'], $data['page']);

        
        Out::jout($data);
    }
}
