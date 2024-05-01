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
        $get = get(['string' => ['word', 'page']]);
        if ($get['word']) {
            $data = M('search', 'im')->search($this->langid, $get['word'], $get['page']);
            $this->view('search_list', ['word' => $get['word'], 'data' => $data]);
        } else {
            $list = T('hot_search')->set_where(['lang' => $this->langid])->order_by(['s' => 'down', 'f' => 'sernum'])->set_limit(15)->get_all();
            $this->view(null, ['data' => $list]);
        }
    }
}
