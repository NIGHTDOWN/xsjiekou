<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class rack extends indexbase
{

    protected $noNeedLogin = [''];


    public function control_run()
    {
        $data = M('rack', 'im')->list($this->get_userid());
        // d($data[0]);
        $this->view(null, ['data' => $data]);
    }
}
