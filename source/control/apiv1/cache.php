<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\Y;
use ng169\tool\Request;

checktop();
class cache extends apiv1base
{

    protected $noNeedLogin = ['*'];
    //删除书籍相关缓存
    public function control_delbook()
    {
        $data = get(['string' => ['type', 'id']]);
        M('book', 'im')->clearcache($data['type'], $data['id']);
    }

    //删除书架缓存
    public function control_delrack()
    {
        $data = get(['string' => ['lang']]);
        M('book', 'im')->clearrackcache($data['lang']);
    }
    //清空缓存
    public function control_clear()
    {
        Y::$cache->clear();
    }
    //删除指定名称的缓存
    public function control_delname()
    {
    }
}
