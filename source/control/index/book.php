<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;
use ng169\Y;

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
        //判断是否已经添加书架

        if ($detail) {
            $detail['inrack'] = M('rack', 'im')->in_rack($this->get_userid(), 1, $get['bookid']);
            $detail['sahretag'] = implode(',', array_column($detail['data']['tags'], 'tag'));
            // d($detail);
            $detail['his'] = M('rack', 'im')->getbookhis($this->get_userid(), 1, $get['bookid']);
            $tjcache = 'tjcache' . $get['bookid'] . $detail['data']['type'];
            list($bool, $cache) = Y::$cache->get($tjcache);

            if ($bool) {
                if (sizeof($cache)) {
                    $detail = array_merge($detail, $cache);
                }
            } else {
                $similars = M('book', 'im')->getsimilar($get['bookid'], $detail['data']['type'], 6);

                $author = M('book', 'im')->getsimilarauthor($get['bookid'], $detail['data']['type'], 3);

                if (sizeof($similars)) {
                    $detailtmp['similar'] = T('book')->set_field('bpic,book_id,1 as type,other_name,lable,lang,`read`')->whereIn('book_id', $similars)->get_all();
                    foreach ($detailtmp['similar']  as $k => $book) {
                        $detailtmp['similar'][$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $book['lang']);
                    }
                }
                if (sizeof($author)) {
                    $detailtmp['author'] = T('book')->set_field('bpic,book_id,1 as type,other_name,lable,lang,`read`')->whereIn('book_id', $author)->get_all();
                    foreach ($detailtmp['author']  as $k => $book) {
                        $detailtmp['author'][$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $book['lang']);
                    }
                }
                if (sizeof($detailtmp)) {
                    $detail = array_merge($detail, $detailtmp);
                    Y::$cache->set($tjcache, $detailtmp, G_DAY * 2);
                }
            }


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
    public function control_get_section()
    {
        $book_id = get(['string' => ['bookid' => 1]]);
        $data = M('content', 'im')->book_section($this->get_userid(), $book_id['bookid']);
        Out::jout($data);
    }
}
