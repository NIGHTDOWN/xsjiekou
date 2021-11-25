<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class book extends indexbase
{

    protected $noNeedLogin = ['*'];


    public function control_run()
    {
        $get = get(['int' => ['bookid']]);
        if (!$get['bookid']) {
            Out::page404();
        }
        $detail = M('book', 'im')->detail($get['bookid'], $this->get_userid());
      
        $this->view(null, $detail);
    }
}
