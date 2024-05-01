<?php

namespace ng169\model\index;

use ng169\tool\Request;
use ng169\Y;

checktop();
//统计埋点
class bookcensus extends Y
{
    private $bookcensus = null;
    private $bookw = null;
    private function init($bid, $type)
    {
        $w2['dates'] = date('Ymd');
        $w2['bid'] = $bid;
        $w2['type'] = $type;
        $this->bookw = $w2;
        $data = T('n_daybook')->set_field('*')->get_one($w2);
        if (!$data) {
            if ($type == 1) {
                $book = T('book')->field('other_name,create_time')->get_one(['book_id' => $bid]);
            } else {
                $book = T('cartoon')->field('other_name,create_time')->get_one(['cartoon_id' => $bid]);
            }
            $w2['name'] = $book['other_name'];
            $w2['addtime'] = time();
            $w2['sjtime'] = $book['create_time'];
            try {
                //code...
                T('n_daybook')->add($w2);
            } catch (\Throwable $th) {
                //throw $th;
            }

            $this->bookcensus = T('n_daybook')->get_one($w2);
        } else {
            $this->bookcensus = $data;
        }
        return $this->bookcensus;
    }
    //阅读人数
    public function readnum($uid, $type, $bookid)
    {

        $this->init($bookid, $type);
        $w['uid'] = $uid ? $uid : ip2long(Request::getip());
        $w['type'] = $type;
        $w['bid'] = $bookid;
        $w['dates'] = date('Ymd');
        if (!T('n_userread')->get_one($w)) {
            try {
                //可能出现并发插入
                T('n_userread')->add($w); //阅读人数去重
                T('n_daybook')->update(['yds' => $this->bookcensus['yds'] + 1], $this->bookw);
                return 1;
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
    public function addgroom($uid, $type, $bookid)
    {
        if ($this->init($bookid, $type)) {
            T('n_daybook')->update('jrsjs=jrsjs+1', $this->bookw);
        }
    }
    public function aword($type, $bookid, $fee)
    {
        if ($this->init($bookid, $type)) {

            T('n_daybook')->update(['dssb' => $this->bookcensus['dssb'] + $fee], $this->bookw);
        }
    }
    public function ordernum($type, $bookid, $fee)
    {
        if ($this->init($bookid, $type)) {

            T('n_daybook')->update(['ddsl' => $this->bookcensus['ddsl'] + 1], $this->bookw);
        }
    }
    public function orderpay($type, $bookid, $fee)
    {
        if ($this->init($bookid, $type)) {

            T('n_daybook')->update(['czje' => $this->bookcensus['czje'] + $fee], $this->bookw);
        }
    }
    //阅读完成
    public function readend($uid, $type, $bookid, $secid)
    {
        $this->init($bookid, $type);
        if ($type == 1) {
            $lang = T('book')->set_field('lang')->set_where(['book_id' => $bookid])->get_one();
            $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
            $sec = T($tpsec)->order_by('section_id desc')->get_one(['book_id' => $bookid]);
            $secids = $sec['section_id'];
        } else {
            $lang = T('cartoon')->set_field('lang')->set_where(['cartoon_id' => $bookid])->get_one();
            $tpsec = M('book', 'im')->gettpsec(2, $lang['lang']);
            $sec = T($tpsec)->order_by('cart_section_id desc')->get_one(['cartoon_id' => $bookid]);
            $secids = $sec['cart_section_id'];
        }
        if ($secid != $secids) {
            return false;
        }
        $w['uid'] = $uid;
        $w['type'] = $type;
        $w['bid'] = $bookid;
        $w['isend'] = 1;
        $w['dates'] = date('Ymd');
        // $w['dates']=date('Ymd');
        if (!T('n_userread')->get_one($w)) {
            T('n_userread')->add($w); //阅读人数去重
            return T('n_daybook')->update(['kws' => $this->bookcensus['kws'] + 1], $this->bookw);
        }
    }
    //解锁数量，消耗书币,章节消耗
    public function unlock($uid, $type, $bookid, $secid, $fee)
    {
        $this->init($bookid, $type);
        $w = $this->bookw;
        $w['secid'] = $secid;
        $w['type'] = $type;
        T('n_daybook')->update(['jsrs' => $this->bookcensus['jsrs'] + 1, 'xhsb' => $this->bookcensus['xhsb'] + $fee], $this->bookw);
        $data = T('n_secread')->get_one($w);
        if ($data) {
            $u['xhsb'] = $data['xhsb'] + $fee;
            T('n_secread')->update($u, $w);
        } else {
            $w['xhsb'] = $data['xhsb'] + $fee;
            T('n_secread')->add($w);
        }
    }
    public function sceread($uid, $type, $bookid, $secid)
    {
        //$this->init($bookid,$type);
        $w = $this->bookw;
        $w['secid'] = $secid;
        $w['type'] = $type;
        $w['uid'] = $uid;
        //$w['bookid']=$bookid;
        if (T('n_usersecread')->get_one($w)) {
            return false;
        }
        $w2 = $w;
        $w2['dates'] = date('Ymd');
        $w2['bookid'] = $bookid;
        $w2['addtime'] = time();
        T('n_usersecread')->add($w2);
        //T('n_daybook')->update(['jsrs' => $this->bookcensus['jsrs']+1,'xhsb'=> $this->bookcensus['xhsb']+$fee], $this->bookw);  
        unset($w['uid']);
        $data = T('n_secread')->get_one($w);
        if ($data) {
            $u['ydrs'] = $data['ydrs'] + 1;
            T('n_secread')->update($u, $w);
        } else {
            $w['ydrs'] = $data['ydrs'] + 1;
            T('n_secread')->add($w);
        }
    }
}
