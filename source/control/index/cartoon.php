<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class cartoon extends indexbase
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
    public function control_new()
    {
        $get = get(['int' => ['page', 'ajax']]);

        $data = M('index', 'im')->booknew(2, $this->langid, 10, $get['page']);
        foreach ($data as $k => $book) {
            $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $this->langid);
            // if ($data[$k]['tags']) {
            //     d($data[$k]);
            // }
        }
        if ($get['ajax']) {
            Out::jout($data);
        } else {
            $this->view('book_new', ['data' => $data]);
        }
    }
}
