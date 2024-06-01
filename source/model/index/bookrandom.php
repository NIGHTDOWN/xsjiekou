<?php

namespace ng169\model\index;

use ng169\Y;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();

class bookrandom extends Y
{
    //获取随机小说
    public function getbook($cityid, $size = 8)
    {
        //先缓存1天所有对应国家书籍id
        //获取数字随机字符串
        //获取对应书籍记录
        $key = 'book';
        $cacheindex = $key . 'allrand';
        $where['lang'] = $cityid;
        $where['status'] = 1;
        $index = $cacheindex .  $cityid;
        $cache = Y::$cache->get($index);
        $alllist = [];
        $ids = [];
        if ($cache[0]) {
            // $this->returnSuccess($cache[1]);
            $alllist = $cache[1];
        } else {
            $data = T($key)
                ->field($key . '_id')
                ->where($where)
                ->get_all();
            $id = array_column($data, $key . '_id');
            if (is_array($id) && sizeof($id)) {
                Y::$cache->set($index, $id, G_DAY);
            }
            $alllist = $id;
            // Y::$cache->set($index, $data, 86400);
        }
        if(sizeof($alllist)>0){
        $randlist = array_rand($alllist, $size);
        for ($i = 0; $i < sizeof($randlist); $i++) {

            array_push($ids, $alllist[$randlist[$i]]);
        }
        $list = T($key)->set_global_where(['status' => 1, 'lang' => $cityid])->field($key . '_id,bpic_dsl,other_name,`desc`,bpic,writer_name,isfree,update_status,1 as type')
            ->wherein($key . '_id', $ids)
            ->get_all();
        return  $list;}
        return [];
    }
    //获取随机漫画
    public function getcartoon($cityid, $size = 8)
    {
        //先缓存1天所有对应国家书籍id
        //获取数字随机字符串
        //获取对应书籍记录
        $key = 'cartoon';
        $cacheindex = $key . 'allrand';
        $where['lang'] = $cityid;
        $where['status'] = 1;
        $index = $cacheindex .  $cityid;
        $cache = Y::$cache->get($index);
        $alllist = [];
        $ids = [];$list=[];
        if ($cache[0]) {
            // $this->returnSuccess($cache[1]);
            $alllist = $cache[1];
        } else {
            $data = T($key)
                ->field($key . '_id')
                ->where($where)
                ->get_all();
            $id = array_column($data, $key . '_id');
            if (is_array($id) && sizeof($id)) {
                Y::$cache->set($index, $id, G_DAY);
            }
            $alllist = $id;
            // Y::$cache->set($index, $data, 86400);
        }
if(sizeof($alllist)>0){


        $randlist = array_rand($alllist, $size);
        for ($i = 0; $i < sizeof($randlist); $i++) {

            array_push($ids, $alllist[$randlist[$i]]);
        }
       
        $list = T('cartoon')->set_global_where([ 'lang' => $cityid,"status"=>1])->field('cartoon_id,bpic_dsl,other_name,`desc`,bpic,writer_name,isfree,update_status,2 as type')
            ->wherein('cartoon_id', $ids)
            ->get_all();}
        return  $list;
    }
}
