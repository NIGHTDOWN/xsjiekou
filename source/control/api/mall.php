<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
use ng169\Y;

checktop();
class mall extends apibase
{
    protected $noNeedLogin = ['*'];
    // 获取漫画首页列表信息
   
    public function control_new_cartoon()
    {
        $get = get(['int' => ['page']]);
        $where['status'] = 1;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $index = 'cartget_cartoon2_:' . $get['page'] . "_" . $cityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $this->returnSuccess($cache[1]);
        } else {
            // recommend_num
            $data = T('cartoon')
                ->field('cartoon_id,other_name,bpic_dsl,bpic,`desc`,hits as recommend_num,writer_name,isfree,update_status,2 as type')
                ->where($where)
                ->order('section desc,hits desc')
                ->limit([$get['page'], 5])
                ->get_all();
            if (is_array($data) && sizeof($data)) {
                Y::$cache->set($index, $data, 46000);
            }
            // Y::$cache->set($index, $data, 86400);
            $this->returnSuccess($data);
        }
    }
    public function control_get_randList()
    {

        // $nums = get(['int' => ['num']]);
        // $nums = $nums['num'] ? $nums['num'] : 8;
        // $cityid = $this->head['cityid'];
        // $list = T('cartoon')->set_global_where(['status' => 1, 'lang' => $cityid])->field('cartoon_id,other_name,`desc`,bpic,writer_name,isfree,update_status,2 as type')->set_limit($nums)->order_by('RAND()')->get_all();

        $size = 500;
        $cityid = $this->head['cityid'];
        $index = "cartrand_" . $cityid;
        $cache = Y::$cache->get($index);
        $lists = [];
        if ($cache[0]) {
            $lists = $cache[1];
        } else {
            $lists = $cache[1];
            $liststmp = T('cartoon')->set_global_where(['status' => 1, 'lang' => $cityid])->field('cartoon_id')->set_limit($size)->order_by('section desc')->get_all();
            $lists = array_column($liststmp, 'cartoon_id');
        }

        $id = array_rand($lists, 8);
        $ids = [];
        for ($i = 0; $i < sizeof($id); $i++) {

            array_push($ids, $lists[$id[$i]]);
        }
        $list = T('cartoon')->set_global_where(['status' => 1, 'lang' => $cityid])->field('cartoon_id,bpic_dsl,other_name,`desc`,bpic,writer_name,isfree,update_status,2 as type')
            ->wherein('cartoon_id', $ids)
            ->get_all();

        Out::jout($list);
    }
    //获取推荐漫画
    public function control_hot_cartoon()
    {
        $get = get(['int' => ['page']]);
        $where['status'] = 1;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $index = 'cartget_cartoon1_' . $get['page'] . "_" . $cityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $this->returnSuccess($cache[1]);
        } else {
            // recommend_num
            $data = T('cartoon')
                ->field('cartoon_id,other_name,bpic,`desc`,bpic_dsl,hits as recommend_num,writer_name,isfree,update_status,2 as type')
                ->where($where)
                ->order('section desc,hits desc')
                ->limit([$get['page'], 5])
                ->get_all();
            if (is_array($data) && sizeof($data)) {
                Y::$cache->set($index, $data, 46000);
            }
            // Y::$cache->set($index, $data, 86400);
            $this->returnSuccess($data);
        }
    }
    

}
