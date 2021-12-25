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
        // d($detail, 1);
        if ($detail) {
            $this->view(null, $detail);
        } else {
            Out::page404();
        }
    }
    public function control_new()
    {
        $get = get(['int' => ['page', 'ajax']]);

        $data = M('index', 'im')->booknew(1, $this->langid, 10, $get['page']);
        foreach ($data as $k => $book) {
            $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $this->langid);
        }
        if ($get['ajax']) {
            Out::jout($data);
        } else {
            $this->view('book_new', ['data' => $data]);
        }
    }
    public function control_hot()
    {
        $get = get(['int' => ['page', 'ajax']]);

        $data = M('index', 'im')->hot(1, $this->langid, 10, $get['page']);
        foreach ($data as $k => $book) {
            $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $this->langid);
        }
        if ($get['ajax']) {
            Out::jout($data);
        } else {
            $this->view('book_hot', ['data' => $data]);
        }
    }
    public function control_end()
    {
        $get = get(['int' => ['page', 'ajax']]);

        $data = M('index', 'im')->end(1, $this->langid, 10, $get['page']);
        foreach ($data as $k => $book) {
            $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $this->langid);
        }
        if ($get['ajax']) {
            Out::jout($data);
        } else {
            $this->view('book_end', ['data' => $data]);
        }
    }
}
