<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class search extends indexbase
{

    protected $noNeedLogin = ['*'];


    public function control_run()
    {


        $this->view();
    }
}
